<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\AttendanceScan;
use App\Models\BlogPost;
use App\Models\Message;
use App\Models\TrainerBooking;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TrainerDashboardController extends Controller
{
    public function home(): View
    {
        $posts = BlogPost::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        return view('trainer.home', [
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

        return view('trainer.check_in', [
            'recentScans' => $recentScans,
            'latestScan' => $latestScan,
            'trainerQrUrl' => $this->qrUrl('trainer'),
        ]);
    }

    public function subscriptions(): View
    {
        $user = auth()->user();

        $bookings = TrainerBooking::query()
            ->with('member')
            ->where('trainer_id', $user->id)
            ->orderByDesc('session_datetime')
            ->get();

        return view('trainer.subscriptions', [
            'bookings' => $bookings,
        ]);
    }

    public function messages(): View
    {
        $trainer = auth()->user();
        $admin = $this->adminUser();

        $messages = collect();

        if ($admin) {
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
        }

        return view('trainer.messages', [
            'admin' => $admin,
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request): RedirectResponse
    {
        $trainer = $request->user();
        $admin = $this->adminUser();

        if (!$admin) {
            return back()->withErrors(['message' => 'Administrator account not found.']);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        Message::create([
            'sender_id' => $trainer->id,
            'recipient_id' => $admin->id,
            'body' => $validated['body'],
        ]);

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
}
