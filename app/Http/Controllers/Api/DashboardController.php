<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceScan;
use App\Models\User;
use App\Models\MemberMembership;
use App\Models\TrainerBooking;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function attendanceReport(Request $request): JsonResponse
    {
        $period = $request->query('period', '7days');
        $days = match ($period) {
            '7days' => 7,
            '1month' => 30,
            '6months' => 180,
            default => 7,
        };

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($startDate && $endDate) {
            $rangeStart = Carbon::parse($startDate)->startOfDay();
            $rangeEnd = Carbon::parse($endDate)->endOfDay();
        } else {
            $rangeStart = Carbon::today()->subDays($days - 1)->startOfDay();
            $rangeEnd = Carbon::today()->endOfDay();
        }

        $reportDays = collect(CarbonPeriod::create($rangeStart, $rangeEnd))
            ->map(fn (Carbon $day) => $day->copy());

        $labels = $reportDays->map(fn ($day) => $day->format('M d'));

        $checkIns = $reportDays->map(fn ($day) => AttendanceScan::query()
            ->whereDate('scanned_at', $day)
            ->where('action', 'check_in')
            ->count());

        $checkOuts = $reportDays->map(fn ($day) => AttendanceScan::query()
            ->whereDate('scanned_at', $day)
            ->where('action', 'check_out')
            ->count());

        $today = Carbon::today();

        $activeCheckIns = AttendanceScan::query()
            ->whereDate('scanned_at', $today)
            ->orderBy('scanned_at')
            ->get(['user_id', 'action'])
            ->groupBy('user_id')
            ->map(fn ($records) => $records->last())
            ->filter(fn ($record) => $record && $record->action === 'check_in')
            ->count();

        $totalMembers = User::query()
            ->where('role', 'user')
            ->count();

        $todayCheckIns = AttendanceScan::query()
            ->whereDate('scanned_at', $today)
            ->where('action', 'check_in')
            ->count();

        $todayCheckOuts = AttendanceScan::query()
            ->whereDate('scanned_at', $today)
            ->where('action', 'check_out')
            ->count();

        $rangeCheckIns = AttendanceScan::query()
            ->whereBetween('scanned_at', [$rangeStart, $rangeEnd])
            ->where('action', 'check_in')
            ->count();

        $rangeCheckOuts = AttendanceScan::query()
            ->whereBetween('scanned_at', [$rangeStart, $rangeEnd])
            ->where('action', 'check_out')
            ->count();

        return response()->json([
            'period' => $period,
            'labels' => $labels,
            'check_ins' => $checkIns,
            'check_outs' => $checkOuts,
            'cards' => [
                'total_members' => $totalMembers,
                'active_check_ins' => $activeCheckIns,
                'today_check_in' => $todayCheckIns,
                'today_check_out' => $todayCheckOuts,
                'range_check_ins' => $rangeCheckIns,
                'range_check_outs' => $rangeCheckOuts,
            ],
        ]);
    }


    public function growthSummary(Request $request): JsonResponse
    {
        $months = max(1, (int) $request->query('months', 6));
        $months = min($months, 24);

        $range = collect(range(0, $months - 1))
            ->map(fn ($offset) => Carbon::now()->subMonths($months - 1 - $offset)->startOfMonth());

        $labels = $range->map(fn (Carbon $month) => $month->format('M Y'));

        $userCounts = $range->map(fn (Carbon $month) => User::query()
            ->where('role', 'user')
            ->whereBetween('created_at', [$month, $month->copy()->endOfMonth()])
            ->count());

        $subscriptionCounts = $range->map(fn (Carbon $month) => MemberMembership::query()
            ->whereBetween('start_date', [$month, $month->copy()->endOfMonth()])
            ->count());

        $trainerBookingCounts = $range->map(fn (Carbon $month) => TrainerBooking::query()
            ->whereBetween('created_at', [$month, $month->copy()->endOfMonth()])
            ->count());

        return response()->json([
            'months' => $months,
            'labels' => $labels,
            'users' => $userCounts,
            'subscriptions' => $subscriptionCounts,
            'trainer_bookings' => $trainerBookingCounts,
        ]);
    }
}
