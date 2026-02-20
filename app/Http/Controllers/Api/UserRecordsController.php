<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class UserRecordsController extends Controller
{
    public function records(int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $isTrainer = $user->role === 'trainer';
            $user->load([
            'subscriptions.plan:id,name',
                $isTrainer
                    ? 'trainerAssignments.member:id,name,email,phone'
                    : 'trainerBookings.trainer:id,name,email,phone',
                $isTrainer
                    ? 'trainerAssignments.trainerPackage:id,name,package_type'
                    : 'trainerBookings.trainerPackage:id,name,package_type',
                $isTrainer
                    ? 'boxingAssignments.member:id,name,email,phone'
                    : 'boxingBookings.trainer:id,name,email,phone',
                $isTrainer
                    ? 'boxingAssignments.boxingPackage:id,name,package_type'
                    : 'boxingBookings.boxingPackage:id,name,package_type',
            ]);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $trainerBookings = $user->role === 'trainer'
            ? $user->trainerAssignments
            : $user->trainerBookings;

        $boxingBookings = $user->role === 'trainer'
            ? $user->boxingAssignments
            : $user->boxingBookings;


        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
            ],
            'subscriptions' => $user->subscriptions->map(function ($subscription) {
                return [
                    ...$subscription->toArray(),
                    'plan_name' => $subscription->plan?->name,
                ];
            })->values(),
            'trainer_bookings' => $trainerBookings->map(function ($booking) {
                return [
                    ...$booking->toArray(),
                    'package_name' => $booking->trainerPackage?->name,
                    'package_type' => $booking->trainerPackage?->package_type,
                ];
            })->values(),
            'boxing_bookings' => $boxingBookings->map(function ($booking) {
                return [
                    ...$booking->toArray(),
                    'package_name' => $booking->boxingPackage?->name,
                    'package_type' => $booking->boxingPackage?->package_type,
                ];
            })->values(),
        ]);
    }
}

/*
Sample response:
{
    "user": {
        "id": 1,
        "name": "Jane Doe",
        "email": "jane@example.com",
        "phone": "+1-555-0100",
        "role": "user"
    },
    "subscriptions": [
        {
            "id": 10,
            "member_id": 1,
            "membership_plan_id": 3,
            "start_date": "2024-09-01",
            "end_date": "2024-10-01",
            "is_on_hold": false,
            "is_expired": false
        }
    ],
    "trainer_bookings": [
        {
            "id": 22,
            "member_id": 1,
            "trainer_id": 5,
            "status": "active"
        }
    ],
    "boxing_bookings": [
        {
            "id": 30,
            "member_id": 1,
            "trainer_id": 8,
            "status": "active"
        }
    ]
}
*/
