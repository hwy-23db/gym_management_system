<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TrainerPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\TrainerBooking;

class TrainerBookingController extends Controller
{
    public function index()
    {
        $bookings = TrainerBooking::query()
            ->with(['member', 'trainer', 'trainerPackage'])
            ->orderByDesc('created_at')
            ->get();

         $members = User::query()
            ->where('role', 'user')
            ->orderBy('name')
            ->get();

         $trainers = User::query()
            ->where('role', 'trainer')
            ->orderBy('name')
            ->get();

         $trainerPackages = TrainerPackage::query()
            ->orderBy('package_type')
            ->orderBy('sessions_count')
            ->orderBy('duration_months')
            ->get();

        return view('pages.trainer-bookings', [
            'bookings' => $bookings,
            'members' => $members,
            'trainers' => $trainers,
            'trainerPackages' => $trainerPackages,
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
            'trainer_package_id' => ['required', Rule::exists('trainer_packages', 'id')],
            'sessions_count' => ['nullable', 'integer', 'min:1'],
            'price_per_session' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'paid_status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $package = TrainerPackage::findOrFail($validated['trainer_package_id']);
        $sessionsCount = $package->sessions_count ?? (int) ($validated['sessions_count'] ?? 1);
        $sessionsCount = max(1, $sessionsCount);
        $pricePerSession = (float) ($package->price / $sessionsCount);

        TrainerBooking::create([
            'member_id' => $validated['member_id'],
            'trainer_id' => $validated['trainer_id'],
            'trainer_package_id' => $package->id,
            'sessions_count' => $sessionsCount,
            'price_per_session' => $pricePerSession,
            'sessions_remaining' => $sessionsCount,
            'total_price' => (float) $package->price,
            'status' => $validated['status'],
            'paid_status' => $validated['paid_status'],
            'paid_at' => $validated['paid_status'] === 'paid' ? now() : null,
            'notes' => $validated['notes'] ?? null,
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

}
