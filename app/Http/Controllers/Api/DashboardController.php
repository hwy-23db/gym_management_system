<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceScan;
use App\Models\User;
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
}
