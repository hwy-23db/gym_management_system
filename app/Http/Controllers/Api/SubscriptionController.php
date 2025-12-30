<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemberMembership;
use App\Models\MembershipPlan;
use App\Models\PricingSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;


class SubscriptionController extends Controller
{

    private const ANNUAL_PLAN_MIN_DAYS = 360;
    private const QUARTERLY_PLAN_MIN_DAYS = 80;
    private const MONTHLY_PLAN_MIN_DAYS = 28;


    public function index(Request $request)
    {
        // If you already protect routes with middleware, you can remove this.
        // abort_unless(auth()->check() && auth()->user()->role === 'administrator', 403);

        $subscriptions = MemberMembership::with(['member', 'plan'])
            ->orderByDesc('id')
            ->get();

        $pricingSetting = PricingSetting::query()->first();

        $today = Carbon::today();

        return response()->json([
            'subscriptions' => $subscriptions->map(function (MemberMembership $subscription) use ($pricingSetting, $today) {
                $durationDays = $subscription->plan?->duration_days ?? 0;
                $price = $this->resolvePlanPrice($durationDays, $pricingSetting);

                $holdDays = 0;
                $adjustedEndDate = $subscription->end_date;

                if ($subscription->is_on_hold && $subscription->hold_started_at && $subscription->end_date) {
                    $holdStartedAt = Carbon::parse($subscription->hold_started_at);
                    $holdDays = max(0, $holdStartedAt->diffInDays($today));
                    $adjustedEndDate = Carbon::parse($subscription->end_date)->addDays($holdDays);
                }

                $isExpired = ! $subscription->is_on_hold
                    && ($subscription->is_expired || ($adjustedEndDate && $adjustedEndDate->lt($today)));
                $status = $subscription->is_on_hold ? 'On Hold' : ($isExpired ? 'Expired' : 'Active');

                return [
                    'id' => $subscription->id,
                    'member_name' => $subscription->member?->name ?? 'Unknown',
                    'plan_name' => $subscription->plan?->name ?? 'Plan',
                    'duration_days' => $durationDays,
                    'price' => $price,
                    'start_date' => optional($subscription->start_date)->toDateString(),
                    'end_date' => optional($adjustedEndDate)->toDateString(),
                    'is_on_hold' => (bool) $subscription->is_on_hold,
                    'status' => $status,
                ];
            }),
        ]);
    }

    public function options()
{
        $members = User::query()
            ->where('role', 'user')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $pricingSetting = PricingSetting::query()->first();
        $plans = MembershipPlan::query()
            ->where('is_active', true)
            ->orderBy('duration_days')
            ->get(['id', 'name', 'duration_days'])
            ->map(function (MembershipPlan $plan) use ($pricingSetting) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'duration_days' => $plan->duration_days,
                    'price' => $this->resolvePlanPrice($plan->duration_days, $pricingSetting),
                ];
            })
            ->values();

        return response()->json([
            'members' => $members,
            'plans' => $plans,
        ]);
}

    public function store(Request $request)
    {
        $data = $request->validate([
        'member_id' => [
        'required',
            Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'user')),
            ],
            'membership_plan_id' => ['required', 'exists:membership_plans,id'],
            'start_date' => ['nullable', 'date'],
        ]);

        $plan = MembershipPlan::query()->findOrFail($data['membership_plan_id']);

        $startDate = isset($data['start_date'])
            ? Carbon::parse($data['start_date'])
            : Carbon::today();

        $endDate = ($plan->duration_days ?? 0) > 0
            ? $startDate->copy()->addDays((int) $plan->duration_days)
            : $startDate->copy();

        $subscription = MemberMembership::create([
            'member_id' => $data['member_id'], // ✅ user id
            'membership_plan_id' => $plan->id,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'is_expired' => false,
            'is_on_hold' => false,
            'hold_started_at' => null,
        ]);

        return response()->json([
            'message' => 'Subscription created successfully.',
            'subscription_id' => $subscription->id,
        ], Response::HTTP_CREATED);
    }

    public function hold(MemberMembership $subscription)
    {
        $today = Carbon::today();

        if ($subscription->is_expired || ($subscription->end_date && $subscription->end_date->lt($today))) {
            return response()->json([
                'message' => 'Expired subscriptions cannot be placed on hold.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($subscription->is_on_hold) {
            return response()->json([
                'message' => 'Subscription is already on hold.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $subscription->update([
            'is_on_hold' => true,
            'hold_started_at' => $today,
        ]);

        return response()->json([
            'message' => 'Subscription placed on hold.',
        ]);
    }

    public function resume(MemberMembership $subscription)
    {
        if (! $subscription->is_on_hold) {
            return response()->json([
                'message' => 'Subscription is not on hold.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $today = Carbon::today();
        $holdStartedAt = $subscription->hold_started_at ? Carbon::parse($subscription->hold_started_at) : $today;
        $holdDays = max(0, $holdStartedAt->diffInDays($today));

        if ($subscription->end_date) {
            $subscription->end_date = Carbon::parse($subscription->end_date)->addDays($holdDays);
        }

        $subscription->is_on_hold = false;
        $subscription->hold_started_at = null;
        $subscription->is_expired = false;
        $subscription->save();

        return response()->json([
            'message' => 'Subscription resumed.',
        ]);
    }

    private function resolvePlanPrice(?int $durationDays, ?PricingSetting $pricingSetting): ?float
    {
        if (! $pricingSetting || ! $durationDays) {
            return null;
        }

        if ($durationDays >= self::ANNUAL_PLAN_MIN_DAYS) {
            return (float) $pricingSetting->annual_subscription_price;
        }

        if ($durationDays >= self::QUARTERLY_PLAN_MIN_DAYS) {
            return (float) $pricingSetting->quarterly_subscription_price;
        }

        if ($durationDays >= self::MONTHLY_PLAN_MIN_DAYS) {
            return (float) $pricingSetting->monthly_subscription_price;
        }

        return null;
    }
}
