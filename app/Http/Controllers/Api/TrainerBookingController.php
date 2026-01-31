<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrainerBooking;
use App\Models\TrainerPackage;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class TrainerBookingController extends Controller
{

    public function index()
    {
        $bookings = TrainerBooking::query()
            ->with(['member', 'trainer', 'trainerPackage'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (TrainerBooking $booking) {
                return [
                    'id' => $booking->id,
                    'member_id' => $booking->member_id,
                    'member_name' => $booking->member?->name ?? 'Unknown',
                    'member_phone' => $booking->member_phone,
                    'trainer_id' => $booking->trainer_id,
                    'trainer_name' => $booking->trainer?->name ?? 'Unknown',
                    'trainer_phone' => $booking->trainer_phone,
                    'trainer_package_id' => $booking->trainer_package_id,
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
                    'price_per_session' => $booking->price_per_session,
                    'sessions_remaining' => $booking->sessions_remaining,
                    'sessions_start_date' => optional($booking->sessions_start_date)->toIso8601String(),
                    'sessions_end_date' => optional($booking->sessions_end_date)->toIso8601String(),
                    'month_start_date' => optional($booking->month_start_date)->toIso8601String(),
                    'month_end_date' => optional($booking->month_end_date)->toIso8601String(),
                    'hold_start_date' => optional($booking->hold_start_date)->toIso8601String(),
                    'hold_end_date' => optional($booking->hold_end_date)->toIso8601String(),
                    'total_hold_days' => $booking->total_hold_days,
                    'total_price' => $booking->total_price,
                    'status' => $booking->status,
                    'paid_status' => $booking->paid_status,
                    'paid_at' => optional($booking->paid_at)->toIso8601String(),
                    'notes' => $booking->notes,
                ];
            });

        return response()->json([
            'bookings' => $bookings,
        ]);
    }


        public function options()
    {
        $members = User::query()
            ->where('role', 'user')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        $trainers = User::query()
            ->where('role', 'trainer')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        $trainerPackages = TrainerPackage::query()
            ->orderBy('package_type')
            ->orderBy('sessions_count')
            ->orderBy('duration_months')
            ->get();

        return response()->json([
            'members' => $members,
            'trainers' => $trainers,
            'trainer_packages' => $trainerPackages,
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
            'trainer_package_id' => ['required', Rule::exists('trainer_packages', 'id')],
            'sessions_count' => ['nullable', 'integer', 'min:1'],
            'price_per_session' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['nullable', 'string'],
            'paid_status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $package = TrainerPackage::findOrFail($validated['trainer_package_id']);
        $isMonthBased = strtolower((string) $package->package_type) === 'monthly';
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $sessionsCount = $package->sessions_count ?? (int) ($validated['sessions_count'] ?? 1);
        $sessionsCount = max(1, $sessionsCount);
        $pricePerSession = (float) ($package->price / $sessionsCount);

        $booking = TrainerBooking::create([
            'member_id' => $validated['member_id'],
            'trainer_id' => $validated['trainer_id'],
            'trainer_package_id' => $package->id,
            'sessions_count' => $sessionsCount,
            'sessions_start_date' => $isMonthBased ? null : $startDate,
            'sessions_end_date' => $isMonthBased ? null : $endDate,
            'month_start_date' => $isMonthBased ? $startDate : null,
            'month_end_date' => $isMonthBased ? $endDate : null,
            'hold_start_date' => null,
            'hold_end_date' => null,
            'total_hold_days' => 0,
            'price_per_session' => $pricePerSession,
            'sessions_remaining' => $sessionsCount,
            'total_price' => (float) $package->price,
            'status' => $validated['status'] ?? 'confirmed',
            'paid_status' => $validated['paid_status'] ?? 'unpaid',
            'paid_at' => $validated['paid_status'] === 'paid' ? now() : null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Trainer booking created successfully.',
            'booking_id' => $booking->id,
        ], Response::HTTP_CREATED);
    }

        public function updateSessions(Request $request, TrainerBooking $booking)
    {
        if (! $booking->isSessionBased()) {
            return response()->json([
                'message' => 'Sessions can only be adjusted for session-based bookings.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validate([
            'sessions_remaining' => ['nullable', 'integer', 'min:0', 'required_without:adjustment'],
            'adjustment' => ['nullable', 'integer', 'required_without:sessions_remaining'],
        ]);

        $newRemaining = $booking->sessions_remaining;

        if (array_key_exists('sessions_remaining', $validated)) {
            $newRemaining = (int) $validated['sessions_remaining'];
        }

        if (array_key_exists('adjustment', $validated)) {
            $newRemaining = $booking->sessions_remaining + (int) $validated['adjustment'];
        }

        if ($newRemaining < 0) {
            return response()->json([
                'message' => 'Remaining sessions cannot be negative.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $booking->update([
            'sessions_remaining' => $newRemaining,
        ]);

        return response()->json([
            'message' => 'Remaining sessions updated.',
            'sessions_remaining' => $booking->sessions_remaining,
        ]);
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

        public function markActive(TrainerBooking $booking)
    {
        if ($booking->status !== 'active') {
            $booking->update([
                'status' => 'active',
            ]);
        }

        return response()->json([
            'message' => 'Booking marked as active.',
        ]);
    }

    public function markHold(TrainerBooking $booking)
    {
        if ($booking->status !== 'on-hold') {
            $booking->update([
                'status' => 'on-hold',
            ]);
        }

        return response()->json([
            'message' => 'Booking marked as hold.',
        ]);
    }

        public function hold(TrainerBooking $booking)
    {
        if (! $booking->isMonthBased()) {
            return response()->json([
                'message' => 'Hold is only available for month-based bookings.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($booking->status === 'completed') {
            return response()->json([
                'message' => 'Completed bookings cannot be placed on hold.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($booking->status === 'on-hold' || $booking->hold_start_date) {
            return response()->json([
                'message' => 'Booking is already on hold.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $today = Carbon::now();

        if ($booking->month_end_date && $booking->month_end_date->lt($today)) {
            return response()->json([
                'message' => 'Completed bookings cannot be placed on hold.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $booking->update([
            'status' => 'on-hold',
            'hold_start_date' => $today,
        ]);

        return response()->json([
            'message' => 'Booking placed on hold.',
        ]);
    }

    public function resume(TrainerBooking $booking)
    {
        if (! $booking->isMonthBased()) {
            return response()->json([
                'message' => 'Resume is only available for month-based bookings.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($booking->status !== 'on-hold' || ! $booking->hold_start_date) {
            return response()->json([
                'message' => 'Booking is not on hold.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $resumeDate = Carbon::now();
        $holdStartedAt = Carbon::parse($booking->hold_start_date);
        $holdDays = max(0, $holdStartedAt->diffInDays($resumeDate));

        $booking->month_end_date = $booking->month_end_date
            ? Carbon::parse($booking->month_end_date)->addDays($holdDays)
            : null;

        $booking->hold_end_date = $resumeDate;
        $booking->total_hold_days = (int) $booking->total_hold_days + $holdDays;
        $booking->hold_start_date = null;
        $booking->status = 'active';
        $booking->save();

        return response()->json([
            'message' => 'Booking resumed.',
            'total_hold_days' => $booking->total_hold_days,
            'month_end_date' => optional($booking->month_end_date)->toIso8601String(),
        ]);
    }
}
