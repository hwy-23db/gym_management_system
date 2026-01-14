<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DashboardReportExporter;
use Illuminate\Http\Request;

class DashboardReportController extends Controller
{
    public function export(Request $request, string $format, DashboardReportExporter $exporter)
    {
        if (! in_array($format, ['excel', 'json'], true)) {
            abort(404);
        }

        $reportData = $exporter->buildReportData();
        $timestamp = now()->format('Ymd_His');

        if ($format === 'json') {
            $json = $exporter->buildJsonDocument($reportData);

            return response($json)
                ->header('Content-Type', 'application/json; charset=UTF-8')
                ->header('Content-Disposition', "attachment; filename=dashboard-report-{$timestamp}.json");
        }

        $excel = $exporter->buildExcelDocument($reportData);

        return response($excel)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=dashboard-report-{$timestamp}.xls");
    }
}
