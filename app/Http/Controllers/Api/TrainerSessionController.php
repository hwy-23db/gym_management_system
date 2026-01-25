<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrainerBooking;
use App\Models\TrainerSessionConfirmation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrainerSessionController extends Controller
{
    public function confirm(Request $request, TrainerBooking $booking): JsonResponse
    {
        $user = $request->user();
        $isMember = $user->role === 'user' && $booking->member_id === $user->id;
        $isTrainer = $user->role === 'trainer' && $booking->trainer_id === $user->id;

        if (! $isMember && ! $isTrainer) {
            return response()->json([
                'message' => 'You are not allowed to confirm this booking.',
            ], 403);
        }

         if ($booking->sessions_remaining !== null && $booking->sessions_remaining <= 0) {
            return response()->json([
                'message' => 'All sessions have already been completed for this booking.',
            ], 422);
        }

        $validated = $request->validate([
            'token' => ['nullable', 'string', 'max:64'],
        ]);

        $token = $validated['token'] ?? null;

        $payload = DB::transaction(function () use ($booking, $isMember, $isTrainer, $token) {
            $lockedBooking = TrainerBooking::query()
                ->whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBooking->sessions_remaining === null) {
                $lockedBooking->sessions_remaining = $lockedBooking->sessions_count;
                $lockedBooking->save();
            }


            if ($lockedBooking->sessions_remaining <= 0) {
                return [
                    'status' => 422,
                    'message' => 'All sessions have already been completed for this booking.',
                ];
            }

            $confirmation = TrainerSessionConfirmation::query()
                ->where('trainer_booking_id', $lockedBooking->id)
                ->whereNull('confirmed_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $confirmation) {
                $confirmationToken = $token ?: strtoupper(Str::random(6));

                $confirmation = TrainerSessionConfirmation::create([
                    'trainer_booking_id' => $lockedBooking->id,
                    'token' => $confirmationToken,
                    'user_confirmed_at' => $isMember ? now() : null,
                    'trainer_confirmed_at' => $isTrainer ? now() : null,
                ]);

                return [
                    'status' => 200,
                    'message' => 'Session confirmation started. Token generated for this session.',
                    'token' => $confirmation->token,
                    'sessions_remaining' => $lockedBooking->sessions_remaining,
                    'sessions_count' => $lockedBooking->sessions_count,
                    'booking_status' => $lockedBooking->status,
                    'completed' => false,
                ];
            }

            if (! $token) {
                 $token = $confirmation->token;
            }

            if (! hash_equals($confirmation->token, $token)) {
                return [
                    'status' => 422,
                    'message' => 'The provided token does not match.',
                ];
            }

            if ($isMember && $confirmation->user_confirmed_at) {
                return [
                    'status' => 422,
                    'message' => 'You have already confirmed this session.',
                ];
            }

            if ($isTrainer && $confirmation->trainer_confirmed_at) {
                return [
                    'status' => 422,
                    'message' => 'You have already confirmed this session.',
                ];
            }

            if ($isMember) {
                $confirmation->user_confirmed_at = now();
            }

            if ($isTrainer) {
                $confirmation->trainer_confirmed_at = now();
            }

            if ($confirmation->user_confirmed_at && $confirmation->trainer_confirmed_at) {
                $confirmation->confirmed_at = now();
                $lockedBooking->sessions_remaining = max(0, $lockedBooking->sessions_remaining - 1);

                if ($lockedBooking->sessions_remaining === 0) {
                    $lockedBooking->status = 'completed';
                }

                $lockedBooking->save();
            }

            $confirmation->save();

            return [
                'status' => 200,
                'message' => $confirmation->confirmed_at
                    ? 'Session confirmed and recorded.'
                    : 'Confirmation recorded. Waiting for the other party.',
                'token' => $confirmation->token,
                'sessions_remaining' => $lockedBooking->sessions_remaining,
                'sessions_count' => $lockedBooking->sessions_count,
                'booking_status' => $lockedBooking->status,
                'completed' => (bool) $confirmation->confirmed_at,
            ];
        });

        return response()->json([
            'message' => $payload['message'],
            'token' => $payload['token'] ?? null,
            'sessions_remaining' => $payload['sessions_remaining'] ?? null,
            'sessions_count' => $payload['sessions_count'] ?? null,
            'booking_status' => $payload['booking_status'] ?? null,
            'completed' => $payload['completed'] ?? null,
        ], $payload['status']);
    }
}
