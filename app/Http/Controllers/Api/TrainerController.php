<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceScan;
use App\Models\BlogPost;
use App\Models\Message;
use App\Models\TrainerBooking;
use App\Notifications\NewMessageNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TrainerController extends Controller
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

        if (! $this->tokenMatches('trainer', $validated['token'])) {
            return response()->json([
                'message' => 'This QR code has expired. Please refresh and try again.',
            ], 422);
        }

        $user = $request->user();

        if (! $user || $user->role !== 'trainer') {
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

        $bookings = TrainerBooking::query()
            ->with(['member', 'trainerPackage'])
            ->where('trainer_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (TrainerBooking $booking) {
                return [
                    'id' => $booking->id,
                    'member' => [
                        'id' => $booking->member?->id,
                        'name' => $booking->member?->name,
                        'email' => $booking->member?->email,
                        'phone' => $booking->member?->phone,
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
                    'paid_status' => $booking->paid_status,
                    'notes' => $booking->notes,
                ];
            });

        return response()->json([
            'bookings' => $bookings,
        ]);
    }

    public function messages(Request $request): JsonResponse
    {
        $trainer = $request->user();
        $admin = $this->adminUser();

        if (! $admin) {
            return response()->json([
                'message' => 'Administrator account not found.',
            ], 404);
        }

        $messages = Message::query()
            ->with('sender')
            ->where(function ($query) use ($trainer, $admin) {
                $query->where('sender_id', $trainer->id)
                    ->where('recipient_id', $admin->id);
            })
            ->orWhere(function ($query) use ($trainer, $admin) {
                $query->where('sender_id', $admin->id)
                    ->where('recipient_id', $trainer->id);
            })
            ->orderBy('created_at')
            ->get();

        Message::where('sender_id', $admin->id)
            ->where('recipient_id', $trainer->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
            ],
            'messages' => $messages->map(function (Message $message) use ($trainer) {
                return [
                    'id' => $message->id,
                    'body' => $message->body,
                    'created_at' => $message->created_at?->toIso8601String(),
                    'is_trainer' => $message->sender_id === $trainer->id,
                    'sender_name' => $message->sender?->name,
                ];
            }),
        ]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $trainer = $request->user();
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
            'sender_id' => $trainer->id,
            'recipient_id' => $admin->id,
            'body' => $validated['body'],
        ]);

        if ($this->shouldNotify($admin)) {
            $admin->notify(new NewMessageNotification($message, $trainer));
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
