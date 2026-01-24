<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AttendanceScan;
use App\Models\BlogPost;
use App\Models\Message;
use App\Models\TrainerBooking;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserDashboardController extends Controller
{
    public function home(): View
    {
        $posts = BlogPost::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        return view('user.home', [
            'posts' => $posts,
        ]);
    }

    public function checkIn(): View
    {
        $user = auth()->user();

        $recentScans = AttendanceScan::query()
            ->where('user_id', $user->id)
            ->orderByDesc('scanned_at')
            ->take(10)
            ->get();

        $latestScan = $recentScans->first();

        return view('user.check_in', [
            'recentScans' => $recentScans,
            'latestScan' => $latestScan,
            'userQrUrl' => $this->qrUrl('user'),
        ]);
    }

    public function subscriptions(): View
    {
        $user = auth()->user();

        $bookings = TrainerBooking::query()
            ->with(['trainer', 'trainerPackage'])
            ->where('member_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('user.subscriptions', [
            'bookings' => $bookings,
        ]);
    }

    public function messages(): View
    {
        $member = auth()->user();
        $admin = $this->adminUser();

        $messages = collect();

        if ($admin) {
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
        }

        return view('user.messages', [
            'admin' => $admin,
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request): RedirectResponse
    {
        $member = $request->user();
        $admin = $this->adminUser();

        if (!$admin) {
            return back()->withErrors(['message' => 'Administrator account not found.']);
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

        return back()->with('status', 'Message sent to admin.');
    }

    private function adminUser(): ?User
    {
        return User::query()
            ->where('role', 'administrator')
            ->first();
    }

    private function qrUrl(string $type): string
    {
        $token = $this->getQrToken($type);

        return url('/attendance/scan?type=' . $type . '&token=' . $token);
    }

    private function getQrToken(string $type): string
    {
        $key = $this->qrTokenKey($type);

        return Cache::rememberForever($key, fn () => Str::random(40));
    }

    private function qrTokenKey(string $type): string
    {
        return 'attendance_qr_token_' . $type;
    }

    private function shouldNotify(User $user): bool
    {
        return $user->role === 'administrator' || $user->notifications_enabled;
    }
}
