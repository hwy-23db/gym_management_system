<?php

namespace App\Support;

use App\Models\AttendanceScan;
use App\Models\MemberMembership;
use App\Models\TrainerBooking;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Collection;

class DashboardReportExporter
{
    public function buildReportData(): array
    {
        return [
            'Users' => User::query()->latest()->get(),
            'Subscriptions' => MemberMembership::with(['member', 'plan'])
                ->latest('start_date')
                ->get(),
            'Trainer Bookings' => TrainerBooking::with(['member', 'trainer'])
                ->latest('session_datetime')
                ->get(),
            'Attendance Scans' => AttendanceScan::with('user')
                ->latest('scanned_at')
                ->get(),
        ];
    }

    public function buildExcelDocument(array $reportData): string
    {
        $worksheets = collect($reportData)->map(function (Collection $rows, string $title) {
            $normalizedRows = $this->normalizeRows($rows);

             return $this->renderWorksheet($title, $normalizedRows);
        })->implode("\n");

         return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
    xmlns:o="urn:schemas-microsoft-com:office:office"
    xmlns:x="urn:schemas-microsoft-com:office:excel"
    xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
    xmlns:html="http://www.w3.org/TR/REC-html40">
    {$worksheets}
</Workbook>
XML;
    }

    public function buildJsonDocument(array $reportData): string
    {
        $normalizedData = collect($reportData)->map(function (Collection $rows) {
            return $rows->map(function ($row) {
                $data = is_array($row) ? $row : $row->toArray();

                return $this->normalizeJsonValue($data);
            })->all();
        })->all();

        return json_encode(
            $normalizedData,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }

    private function normalizeRows(Collection $rows): array
    {
        return $rows->map(function ($row) {
            $data = is_array($row) ? $row : $row->toArray();

            return collect($data)->map(function ($value) {
                return $this->stringifyValue($value);
            })->all();
        })->all();
    }

    private function stringifyValue($value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) ($value ?? '');
    }

        private function normalizeJsonValue($value)
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => $this->normalizeJsonValue($item))
                ->all();
        }

        if (is_bool($value)) {
            return $value;
        }


                return $value ?? '';
    }

    private function renderWorksheet(string $title, array $rows): string
    {
        $worksheetName = $this->sanitizeWorksheetName($title);


                       if (count($rows) === 0) {
            $rowsXml = $this->renderWorksheetRow(['No data available.']);
        } else {
            $headers = array_keys($rows[0]);
            $rowsXml = $this->renderWorksheetRow($headers);

            foreach ($rows as $row) {
                $rowValues = array_map(
                    fn (string $header) => $row[$header] ?? '',
                    $headers
                );
                $rowsXml .= $this->renderWorksheetRow($rowValues);
            }
        }

        return <<<XML
        <Worksheet ss:Name="{$worksheetName}">
            <Table>
                {$rowsXml}
            </Table>
        </Worksheet>
        XML;
    }

           private function renderWorksheetRow(array $values): string
    {
        $cells = collect($values)->map(function ($value) {
            $escapedValue = htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8');

            return <<<XML
<Cell><Data ss:Type="String">{$escapedValue}</Data></Cell>
XML;
        })->implode('');

          return "<Row>{$cells}</Row>";
    }

    private function sanitizeWorksheetName(string $title): string
    {
        $cleaned = preg_replace('/[:\\\\\\/\\?\\*\\[\\]]/', ' ', $title);
        $cleaned = trim((string) $cleaned);

        return mb_substr($cleaned === '' ? 'Sheet' : $cleaned, 0, 31);
    }
}
