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

    private const CLASS_PLAN_NAME = 'Class';
    private const CLASS_MIN_DAYS = 30 ;
    private const ANNUAL_PLAN_MIN_DAYS = 360;
    private const SIX_MONTH_PLAN_MIN_DAYS = 180;
    private const THREE_MONTH_PLAN_MIN_DAYS = 90;
    private const MONTHLY_PLAN_MIN_DAYS = 30;


    public function index(Request $request)
    {
        // If you already protect routes with middleware, you can remove this.
        // abort_unless(auth()->check() && auth()->user()->role === 'administrator', 403);

        $subscriptions = MemberMembership::with(['member', 'plan'])
            ->orderByDesc('id')
            ->get();

        $pricingSetting = PricingSetting::query()->firstOrCreate(
            [],
            [
                'class_subscription_price' => 70000,
                'monthly_subscription_price' => 80000,
                'three_month_subscription_price' => 240000,
                'quarterly_subscription_price' => 400000,
                'annual_subscription_price' => 960000,
            ]
        );

        $today = Carbon::today();

        return response()->json([
            'subscriptions' => $subscriptions->map(function (MemberMembership $subscription) use ($pricingSetting, $today) {
                $durationDays = $subscription->plan?->duration_days ?? 0;
                $price = $this->resolvePlanPrice($subscription->plan?->name, $durationDays, $pricingSetting);
                $discountPercentage = $subscription->discount_percentage !== null
                    ? (float) $subscription->discount_percentage
                    : null;
                $storedFinalPrice = $subscription->final_price !== null
                    ? (float) $subscription->final_price
                    : null;

                $finalPrice = $storedFinalPrice;

                if (
                    ($finalPrice === null || $finalPrice <= 0)
                    && ($discountPercentage === null || $discountPercentage <= 0)
                    && $price !== null
                ) {
                    $finalPrice = (float) $price;
                }

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
                    'member_phone' => $subscription->member?->phone,
                    'plan_name' => $subscription->plan?->name ?? 'Plan',
                    'duration_days' => $durationDays,
                    'price' => $price,
                    'discount_percentage' => $discountPercentage,
                    'final_price' => $finalPrice ?? 0,
                    'created_at' => optional($subscription->created_at)->toIso8601String(),
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
            ->get(['id', 'name', 'email', 'phone']);

        $pricingSetting = PricingSetting::query()->firstOrCreate(
            [],
            [
                'class_subscription_price' => 70000,
                'monthly_subscription_price' => 80000,
                'three_month_subscription_price' => 240000,
                'quarterly_subscription_price' => 400000,
                'annual_subscription_price' => 960000,
            ]
        );
        $plans = $this->syncPricingPlans($pricingSetting);

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
            'discount_percentage' => ['nullable', 'numeric', 'between:0,100'],
        ]);

        $plan = MembershipPlan::query()->findOrFail($data['membership_plan_id']);

        $startDate = isset($data['start_date'])
            ? Carbon::parse($data['start_date'])
            : Carbon::today();

        $endDate = ($plan->duration_days ?? 0) > 0
            ? $startDate->copy()->addDays((int) $plan->duration_days)
            : $startDate->copy();

        $pricingSetting = PricingSetting::query()->firstOrCreate(
            [],
            [
                'class_subscription_price' => 70000,
                'monthly_subscription_price' => 80000,
                'three_month_subscription_price' => 240000,
                'quarterly_subscription_price' => 400000,
                'annual_subscription_price' => 960000,
            ]
        );

        $price = $this->resolvePlanPrice($plan->name, $plan->duration_days, $pricingSetting) ?? 0;
        $hasDiscount = array_key_exists('discount_percentage', $data)
            && $data['discount_percentage'] !== null
            && $data['discount_percentage'] !== '';

        $discountPercentage = $hasDiscount
            ? (float) $data['discount_percentage']
            : null;

        $finalPrice = $hasDiscount
            ? $price - ($price * ($discountPercentage / 100))
            : $price;

        $subscription = MemberMembership::create([
            'member_id' => $data['member_id'], // ✅ user id
            'membership_plan_id' => $plan->id,
            'discount_percentage' => $discountPercentage !== null ? round($discountPercentage, 2) : null,
            'final_price' => round($finalPrice, 2),
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

    private function resolvePlanPrice(?string $planName, ?int $durationDays, ?PricingSetting $pricingSetting): ?float
    {
        if (! $pricingSetting || ! $durationDays) {
            return null;
        }

        if ($planName === self::CLASS_PLAN_NAME) {
            return (float) $pricingSetting->class_subscription_price;
        }


        if ($durationDays >= self::ANNUAL_PLAN_MIN_DAYS) {
            return (float) $pricingSetting->annual_subscription_price;
        }

        if ($durationDays >= self::SIX_MONTH_PLAN_MIN_DAYS) {
            return (float) $pricingSetting->quarterly_subscription_price;
        }

        if ($durationDays >= self::THREE_MONTH_PLAN_MIN_DAYS) {
            return (float) $pricingSetting->three_month_subscription_price;
        }


        if ($durationDays >= self::MONTHLY_PLAN_MIN_DAYS) {
            return (float) $pricingSetting->monthly_subscription_price;
        }

        return null;
    }
        private function syncPricingPlans(PricingSetting $pricingSetting): array
    {
        $definitions = [
            [
                'name' => self::CLASS_PLAN_NAME,
                'duration_days' => self::CLASS_MIN_DAYS,
                'price' => (float) $pricingSetting->class_subscription_price,
            ],
            [
                'name' => '1 Month',
                'duration_days' => self::MONTHLY_PLAN_MIN_DAYS,
                'price' => (float) $pricingSetting->monthly_subscription_price,
            ],
            [
                'name' => '3 Months',
                'duration_days' => self::THREE_MONTH_PLAN_MIN_DAYS,
                'price' => (float) $pricingSetting->three_month_subscription_price,
            ],
            [
                'name' => '6 Months',
                'duration_days' => self::SIX_MONTH_PLAN_MIN_DAYS,
                'price' => (float) $pricingSetting->quarterly_subscription_price,
            ],
            [
                'name' => '12 Months',
                'duration_days' => self::ANNUAL_PLAN_MIN_DAYS,
                'price' => (float) $pricingSetting->annual_subscription_price,
            ],
        ];

        return collect($definitions)->map(function (array $definition) {
            $plan = MembershipPlan::query()->updateOrCreate(
                ['name' => $definition['name']],
                [
                    'duration_days' => $definition['duration_days'],
                    'is_active' => true,
                ]
            );

            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'duration_days' => $plan->duration_days,
                'price' => $definition['price'],
            ];
        })->values()->all();
    }
}
