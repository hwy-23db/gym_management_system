<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrainerBooking;
use App\Models\TrainerPackage;
use App\Models\User;
use App\Notifications\NewMessageNotification;
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
            'sessions_count' => ['required', 'integer', 'min:1'],
            'price_per_session' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string'],
            'paid_status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $package = TrainerPackage::findOrFail($validated['trainer_package_id']);
        $sessionsCount = $package->sessions_count ?? max(1, (int) $validated['sessions_count']);
        $pricePerSession = (float) ($package->price / max(1, $sessionsCount));

        $booking = TrainerBooking::create([
            'member_id' => $validated['member_id'],
            'trainer_id' => $validated['trainer_id'],
            'trainer_package_id' => $package->id,
            'sessions_count' => $sessionsCount,
            'price_per_session' => $pricePerSession,
            'total_price' => (float) $package->price,
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
}
