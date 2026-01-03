<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TrainerBooking;
use App\Models\TrainerPricing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainerBookingController extends Controller
{
    private const DEFAULT_PRICE_PER_SESSION = 30000;

    public function index()
    {
        $bookings = TrainerBooking::query()
            ->with(['member', 'trainer'])
            ->orderByDesc('session_datetime')
            ->get();

         $members = User::query()
            ->where('role', 'user')
            ->orderBy('name')
            ->get();

         $trainers = User::query()
            ->where('role', 'trainer')
            ->orderBy('name')
            ->get();

        $trainerPrices = TrainerPricing::query()
            ->whereIn('trainer_id', $trainers->pluck('id'))
            ->get()
            ->keyBy('trainer_id');

        return view('pages.trainer-bookings', [
            'bookings' => $bookings,
            'members' => $members,
            'trainers' => $trainers,
            'trainerPrices' => $trainerPrices,
            'defaultTrainerPrice' => self::DEFAULT_PRICE_PER_SESSION,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'user')),
            ],
            'trainer_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'trainer')),
            ],
            'session_datetime' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'sessions_count' => ['required', 'integer', 'min:1'],
            'price_per_session' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'paid_status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $pricePerSession = $validated['price_per_session'] ?? $this->resolveTrainerPrice($validated['trainer_id']);
        $sessionsCount = (int) $validated['sessions_count'];

        TrainerBooking::create([
            'member_id' => $validated['member_id'],
            'trainer_id' => $validated['trainer_id'],
            'session_datetime' => $validated['session_datetime'],
            'duration_minutes' => $validated['duration_minutes'],
            'sessions_count' => $sessionsCount,
            'price_per_session' => $pricePerSession,
            'total_price' => $pricePerSession * $sessionsCount,
            'status' => $validated['status'],
            'paid_status' => $validated['paid_status'],
            'paid_at' => $validated['paid_status'] === 'paid' ? now() : null,
            'notes' => $validated['notes'],
        ]);

        return back()->with('status', 'Trainer booking created.');
    }

    public function markPaid(TrainerBooking $booking): RedirectResponse
    {
        if ($booking->paid_status !== 'paid') {
                $booking->update([
                'paid_status' => 'paid',
                'paid_at' => now(),
            ]);
        }

        return back()->with('status', 'Booking marked as paid.');
    }

    private function resolveTrainerPrice(int $trainerId): float
    {
        $pricing = TrainerPricing::query()
            ->where('trainer_id', $trainerId)
            ->first();

        return (float) ($pricing?->price_per_session ?? self::DEFAULT_PRICE_PER_SESSION);
    }
}
