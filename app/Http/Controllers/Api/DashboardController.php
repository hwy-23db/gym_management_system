<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceScan;
use Carbon\Carbon;
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

        $reportDays = collect(range(0, $days - 1))->map(function ($offset) use ($days) {
            return Carbon::today()->subDays(($days - 1) - $offset);
        });

        $labels = $reportDays->map(fn ($day) => $day->format('M d'));

        $checkIns = $reportDays->map(fn ($day) => AttendanceScan::query()
            ->whereDate('scanned_at', $day)
            ->where('action', 'check_in')
            ->count());

        $checkOuts = $reportDays->map(fn ($day) => AttendanceScan::query()
            ->whereDate('scanned_at', $day)
            ->where('action', 'check_out')
            ->count());

        return response()->json([
            'period' => $period,
            'labels' => $labels,
            'check_ins' => $checkIns,
            'check_outs' => $checkOuts,
        ]);
    }
}
