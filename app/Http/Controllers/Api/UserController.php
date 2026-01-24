<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceScan;
use App\Models\BlogPost;
use App\Models\Message;
use App\Models\MemberMembership;
use App\Models\TrainerBooking;
use App\Models\User;
use App\Models\PricingSetting;
use App\Notifications\NewMessageNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    public function home(): JsonResponse
    {
        $posts = BlogPost::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (BlogPost $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'summary' => $post->summary,
                'content' => $post->content,
                'published_at' => $post->published_at?->toIso8601String(),
                'created_at' => $post->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'posts' => $posts,
        ]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $user = $request->user();

        $recentScans = AttendanceScan::query()
            ->where('user_id', $user->id)
            ->orderByDesc('scanned_at')
            ->take(10)
            ->get();

        $latestScan = $recentScans->first();

        return response()->json([
            'latest_scan' => $latestScan ? $this->scanPayload($latestScan) : null,
            'recent_scans' => $recentScans->map(fn (AttendanceScan $scan) => $this->scanPayload($scan)),
        ]);
    }

    public function scanFromQr(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        if (! $this->tokenMatches('user', $validated['token'])) {
            return response()->json([
                'message' => 'This QR code has expired. Please refresh and try again.',
            ], 422);
        }

        $user = $request->user();

        if (! $user || $user->role !== 'user') {
            return response()->json([
                'message' => 'Unable to record attendance for this account.',
            ], 422);
        }

        $scan = $this->recordScan($user);

        return response()->json([
            'message' => $scan->action === 'check_in'
                ? 'Check-in recorded successfully.'
                : 'Check-out recorded successfully.',
            'record' => $this->scanPayload($scan),
        ]);
    }

    public function subscriptions(Request $request): JsonResponse
    {
        $user = $request->user();

        $subscriptions = MemberMembership::query()
            ->with('plan')
            ->where('member_id', $user->id)
            ->orderByDesc('start_date')
            ->get();

        $pricingSetting = PricingSetting::query()->firstOrCreate(
            [],
            [
                'monthly_subscription_price' => 80000,
                'quarterly_subscription_price' => 240000,
                'annual_subscription_price' => 960000,
            ]
        );

        $today = Carbon::today();

        return response()->json([
            'subscriptions' => $subscriptions->map(function (MemberMembership $subscription) use ($pricingSetting, $today) {
                $durationDays = $subscription->plan?->duration_days ?? 0;
                $price = $this->resolvePlanPrice($durationDays, $pricingSetting);

                $holdDays = 0;
                $adjustedEndDate = $subscription->end_date;

                if ($subscription->is_on_hold && $subscription->hold_started_at && $subscription->end_date) {
                    $holdStartedAt = Carbon::parse($subscription->hold_started_at);
                    $holdDays = max(0, $holdStartedAt->diffInDays($today));
                    $adjustedEndDate = Carbon::parse($subscription->end_date)->addDays($holdDays);
                }

                $isExpired = ! $subscription->is_on_hold
                    && ($subscription->is_expired || ($adjustedEndDate && $adjustedEndDate->lt($today)));
                $status = $subscription->is_on_hold ? 'On Hold' : ($isExpired ? 'Expired' : 'Active');

                return [
                    'id' => $subscription->id,
                    'plan_name' => $subscription->plan?->name ?? 'Plan',
                    'duration_days' => $durationDays,
                    'price' => $price,
                    'created_at' => optional($subscription->created_at)->toIso8601String(),
                    'start_date' => optional($subscription->start_date)->toDateString(),
                    'end_date' => optional($adjustedEndDate)->toDateString(),
                    'is_on_hold' => (bool) $subscription->is_on_hold,
                    'status' => $status,
                ];
            }),
        ]);
    }

    public function bookings(Request $request): JsonResponse
    {
        $user = $request->user();


        $bookings = TrainerBooking::query()
            ->with(['trainer', 'trainerPackage'])
            ->where('member_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (TrainerBooking $booking) {
                return [
                    'id' => $booking->id,
                    'trainer' => [
                        'id' => $booking->trainer?->id,
                        'name' => $booking->trainer?->name,
                        'phone' => $booking->trainer?->phone,
                        'email' => $booking->trainer?->email,
                    ],
                    'trainer_package' => $booking->trainerPackage
                        ? [
                            'id' => $booking->trainerPackage->id,
                            'name' => $booking->trainerPackage->name,
                            'package_type' => $booking->trainerPackage->package_type,
                            'sessions_count' => $booking->trainerPackage->sessions_count,
                            'duration_months' => $booking->trainerPackage->duration_months,
                            'price' => (float) $booking->trainerPackage->price,
                        ]
                        : null,
                    'sessions_count' => $booking->sessions_count,
                    'status' => $booking->status,
                ];
            });

        return response()->json([
            'bookings' => $bookings,
        ]);
    }

    public function messages(Request $request): JsonResponse
    {
        $member = $request->user();
        $admin = $this->adminUser();

        if (! $admin) {
            return response()->json([
                'message' => 'Administrator account not found.',
            ], 404);
        }

        $messages = Message::query()
            ->with('sender')
            ->where(function ($query) use ($member, $admin) {
                $query->where('sender_id', $member->id)
                    ->where('recipient_id', $admin->id);
            })
            ->orWhere(function ($query) use ($member, $admin) {
                $query->where('sender_id', $admin->id)
                    ->where('recipient_id', $member->id);
            })
            ->orderBy('created_at')
            ->get();

        Message::where('sender_id', $admin->id)
            ->where('recipient_id', $member->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
            ],
            'messages' => $messages->map(function (Message $message) use ($member) {
                return [
                    'id' => $message->id,
                    'body' => $message->body,
                    'created_at' => $message->created_at?->toIso8601String(),
                    'is_user' => $message->sender_id === $member->id,
                    'sender_name' => $message->sender?->name,
                ];
            }),
        ]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $member = $request->user();
        $admin = $this->adminUser();

        if (! $admin) {
            return response()->json([
                'message' => 'Administrator account not found.',
            ], 404);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = Message::create([
            'sender_id' => $member->id,
            'recipient_id' => $admin->id,
            'body' => $validated['body'],
        ]);

        if ($this->shouldNotify($admin)) {
            $admin->notify(new NewMessageNotification($message, $member));
        }

        return response()->json([
            'status' => 'sent',
        ], 201);
    }

    private function scanPayload(AttendanceScan $scan): array
    {
        return [
            'action' => $scan->action,
            'timestamp' => $scan->scanned_at?->toIso8601String(),
        ];
    }

    private function resolvePlanPrice(?int $durationDays, ?PricingSetting $pricingSetting): ?float
    {
        if (! $pricingSetting || ! $durationDays) {
            return null;
        }

        if ($durationDays >= 360) {
            return (float) $pricingSetting->annual_subscription_price;
        }

        if ($durationDays >= 80) {
            return (float) $pricingSetting->quarterly_subscription_price;
        }

        if ($durationDays >= 28) {
            return (float) $pricingSetting->monthly_subscription_price;
        }

        return null;
    }


    private function recordScan(User $user): AttendanceScan
    {
        $lastScan = AttendanceScan::query()
            ->where('user_id', $user->id)
            ->whereDate('scanned_at', Carbon::today())
            ->orderByDesc('scanned_at')
            ->first();

        $nextAction = $lastScan && $lastScan->action === 'check_in'
            ? 'check_out'
            : 'check_in';

        return AttendanceScan::create([
            'user_id' => $user->id,
            'action' => $nextAction,
            'scanned_at' => Carbon::now(),
        ]);
    }

    private function tokenMatches(string $type, string $token): bool
    {
        $expected = Cache::get($this->qrTokenKey($type));

        return $expected && hash_equals($expected, $token);
    }

    private function qrTokenKey(string $type): string
    {
        return 'attendance_qr_token_' . $type;
    }

    private function shouldNotify(User $user): bool
    {
        return $user->role === 'administrator' || $user->notifications_enabled;
    }

    private function adminUser(): ?User
    {
        return User::query()
            ->where('role', 'administrator')
            ->first();
    }
}
