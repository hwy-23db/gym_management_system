<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrainerBooking;
use App\Models\TrainerPricing;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class TrainerBookingController extends Controller
{
    private const DEFAULT_PRICE_PER_SESSION = 30000;

    public function index()
    {
        $bookings = TrainerBooking::query()
            ->with(['member', 'trainer'])
            ->orderByDesc('session_datetime')
            ->get()
            ->map(function (TrainerBooking $booking) {
                return [
                    'id' => $booking->id,
                    'member_id' => $booking->member_id,
                    'member_name' => $booking->member?->name ?? 'Unknown',
                    'trainer_id' => $booking->trainer_id,
                    'trainer_name' => $booking->trainer?->name ?? 'Unknown',
                    'session_datetime' => optional($booking->session_datetime)->toDateTimeString(),
                    'duration_minutes' => $booking->duration_minutes,
                    'sessions_count' => $booking->sessions_count,
                    'price_per_session' => $booking->price_per_session,
                    'total_price' => $booking->total_price,
                    'status' => $booking->status,
                    'paid_status' => $booking->paid_status,
                    'notes' => $booking->notes,
                ];
            });

        return response()->json([
            'bookings' => $bookings,
        ]);
    }

    public function store(Request $request)
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
            'status' => ['nullable', 'string'],
            'paid_status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $pricePerSession = $validated['price_per_session'] ?? $this->resolveTrainerPrice($validated['trainer_id']);
        $sessionsCount = (int) $validated['sessions_count'];

        $booking = TrainerBooking::create([
            'member_id' => $validated['member_id'],
            'trainer_id' => $validated['trainer_id'],
            'session_datetime' => $validated['session_datetime'],
            'duration_minutes' => $validated['duration_minutes'],
            'sessions_count' => $sessionsCount,
            'price_per_session' => $pricePerSession,
            'total_price' => $pricePerSession * $sessionsCount,
            'status' => $validated['status'] ?? 'confirmed',
            'paid_status' => $validated['paid_status'] ?? 'unpaid',
            'paid_at' => $validated['paid_status'] === 'paid' ? now() : null,
            'notes' => $validated['notes'],
        ]);

        return response()->json([
            'message' => 'Trainer booking created successfully.',
            'booking_id' => $booking->id,
        ], Response::HTTP_CREATED);
    }

    public function markPaid(TrainerBooking $booking)
    {
        if ($booking->paid_status !== 'paid') {
                 $booking->update([
                'paid_status' => 'paid',
                'paid_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Booking marked as paid.',
        ]);
    }

    private function resolveTrainerPrice(int $trainerId): float
    {
        $pricing = TrainerPricing::query()
            ->where('trainer_id', $trainerId)
            ->first();

        return (float) ($pricing?->price_per_session ?? self::DEFAULT_PRICE_PER_SESSION);
    }
}
