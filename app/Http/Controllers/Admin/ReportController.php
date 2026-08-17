<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\AttendanceReason;
use App\Models\AttendanceSetting;
use App\Models\Employee;
use App\Models\Location;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const WORKING_DAYS = 10;

    public function index(Request $request)
    {
        $start = $this->resolveFortnightStart($request);
        $report = $this->buildRows($request, $start);

        return view('admin.attendance.reports', [
            'start' => $start,
            'workingDays' => $report['workingDays'],
            'rows' => $report['rows'],
            'totals' => $report['totals'],
            'locations' => $this->availableLocations($request),
        ]);
    }

    public function download(Request $request): Response
    {
        $format = $request->query('format', 'excel');
        $start = $this->resolveFortnightStart($request);
        $report = $this->buildRows($request, $start);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.attendance.reports-pdf', [
                'start' => $start,
                'workingDays' => $report['workingDays'],
                'rows' => $report['rows'],
                'totals' => $report['totals'],
            ]);

            return $pdf->download('attendance-report-'.$start->format('Y-m-d').'.pdf');
        }

        return $this->excelDownload($start, $report['workingDays'], $report['rows'], $report['totals']);
    }

    public function detailed(Request $request)
    {
        $data = $this->detailedReportData($request);

        return view('admin.attendance.reports-detailed', [
            'rows' => $data['rows'],
            'summary' => $data['summary'],
            'from' => $data['from'],
            'to' => $data['to'],
            'employees' => $this->availableEmployees($request),
            'departments' => $this->availableDepartments($request),
            'locations' => $this->availableLocations($request),
            'reasons' => AttendanceReason::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function detailedDownload(Request $request): Response
    {
        $format = $request->query('format', 'excel');
        $data = $this->detailedReportData($request);

        if ($format === 'csv') {
            return $this->detailedCsvDownload($data['rows']);
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.attendance.reports-detailed-pdf', [
                'rows' => $data['rows'],
                'summary' => $data['summary'],
                'from' => $data['from'],
                'to' => $data['to'],
            ]);

            return $pdf->download('attendance-detailed-report-'.$data['from']->format('Y-m-d').'.pdf');
        }

        return $this->detailedExcelDownload($data['rows'], $data['from'], $data['to']);
    }

    private function detailedReportData(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : today()->subDays(29)->startOfDay();
        $to = $request->filled('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : today()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $setting = AttendanceSetting::current();
        $user = $request->user();

        $employeesQuery = Employee::query()->with('homeLocation');
        $logsQuery = AttendanceLog::query()
            ->with(['employee.homeLocation', 'location', 'lateReason', 'earlyClockOutReason'])
            ->whereBetween('scanned_at', [$from, $to]);

        if (! $user->isAdmin()) {
            $employeesQuery->where('home_location_id', $user->location_id);
            $logsQuery->where('location_id', $user->location_id);
        } elseif ($request->filled('location_id')) {
            $employeesQuery->where('home_location_id', $request->integer('location_id'));
            $logsQuery->where('location_id', $request->integer('location_id'));
        }

        if ($request->filled('department')) {
            $employeesQuery->where('department', $request->query('department'));
        }

        if ($request->filled('employee_id')) {
            $employeesQuery->where('id', $request->integer('employee_id'));
        }

        if ($request->filled('search')) {
            $term = trim($request->query('search'));
            $employeesQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('employee_code', 'like', "%{$term}%");
            });
        }

        $employees = $employeesQuery->orderBy('name')->get();
        $groupedLogs = $logsQuery->get()->groupBy('employee_id');

        $lateOnly = $request->boolean('late_only');
        $earlyOnly = $request->boolean('early_only');
        $reasonFilter = $request->filled('reason_id') ? $request->integer('reason_id') : null;

        $rows = [];
        $summary = ['late' => 0, 'early' => 0, 'records' => 0];

        foreach ($employees as $employee) {
            $employeeLogs = $groupedLogs->get($employee->id, collect())
                ->sortBy('scanned_at')
                ->groupBy(fn (AttendanceLog $log) => $log->scanned_at->toDateString());

            foreach ($employeeLogs as $date => $dayLogs) {
                $timeIn = $dayLogs->where('type', 'time_in')->sortBy('scanned_at')->first();
                $timeOut = $dayLogs->where('type', 'time_out')->sortByDesc('scanned_at')->first();

                if (! $timeIn) {
                    continue;
                }

                $isLate = $timeIn->scanned_at->gt($setting->shiftStartCarbon($date));
                $isEarly = $timeOut && $timeOut->scanned_at->lt($setting->shiftEndCarbon($date));

                $hours = ($timeIn && $timeOut && $timeOut->scanned_at->gt($timeIn->scanned_at))
                    ? round($timeIn->scanned_at->diffInMinutes($timeOut->scanned_at) / 60, 2)
                    : null;

                if ($lateOnly && ! $isLate) {
                    continue;
                }
                if ($earlyOnly && ! $isEarly) {
                    continue;
                }
                if ($reasonFilter !== null
                    && ! in_array($reasonFilter, [$timeIn->late_reason_id, $timeOut?->early_clock_out_reason_id], true)) {
                    continue;
                }

                $rows[] = [
                    'employee' => $employee,
                    'date' => $date,
                    'time_in' => $timeIn->scanned_at,
                    'time_out' => $timeOut?->scanned_at,
                    'hours' => $hours,
                    'status' => $isLate ? 'late' : 'present',
                    'is_late' => $isLate,
                    'is_early_clock_out' => $isEarly,
                    'late_reason' => $timeIn->lateReason?->name,
                    'late_reason_text' => $timeIn->late_reason_text,
                    'early_reason' => $timeOut?->earlyClockOutReason?->name,
                    'early_reason_text' => $timeOut?->early_clock_out_reason_text,
                ];

                $summary['late'] += $isLate ? 1 : 0;
                $summary['early'] += $isEarly ? 1 : 0;
                $summary['records']++;
            }
        }

        usort($rows, fn ($a, $b) => [$b['date'] <=> $a['date'], strcmp($a['employee']->name, $b['employee']->name)]);

        return [
            'rows' => $rows,
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
        ];
    }

    private function availableEmployees(Request $request)
    {
        $query = Employee::query()->where('active', true);

        if (! $request->user()->isAdmin()) {
            $query->where('home_location_id', $request->user()->location_id);
        } elseif ($request->filled('location_id')) {
            $query->where('home_location_id', $request->integer('location_id'));
        }

        return $query->orderBy('name')->get(['id', 'name', 'employee_code']);
    }

    private function availableDepartments(Request $request)
    {
        $query = Employee::query()->whereNotNull('department')->where('department', '!=', '');

        if (! $request->user()->isAdmin()) {
            $query->where('home_location_id', $request->user()->location_id);
        } elseif ($request->filled('location_id')) {
            $query->where('home_location_id', $request->integer('location_id'));
        }

        return $query->distinct()->orderBy('department')->pluck('department')->values();
    }

    private function detailedExcelDownload(array $rows, Carbon $from, Carbon $to): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detailed Report');

        $sheet->setCellValue('A1', 'Detailed Attendance Report - '.$from->format('F d, Y').' to '.$to->format('F d, Y'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $headers = [
            'Employee Name',
            'Employee ID',
            'Department',
            'Date',
            'Clock-In Time',
            'Clock-Out Time',
            'Hours Worked',
            'Attendance Status',
            'Late Arrival',
            'Early Clock-Out',
            'Selected Reason (Late)',
            'Custom Reason (Late)',
            'Selected Reason (Early)',
            'Custom Reason (Early)',
        ];

        $headerRow = 3;
        foreach ($headers as $col => $header) {
            $coord = Coordinate::stringFromColumnIndex($col + 1).$headerRow;
            $sheet->setCellValue($coord, $header);
            $sheet->getStyle($coord)->getFont()->setBold(true);
            $sheet->getStyle($coord)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E2B47');
            $sheet->getStyle($coord)->getFont()->getColor()->setARGB('FFFFFFFF');
        }

        $rowIndex = $headerRow + 1;
        foreach ($rows as $row) {
            $sheet->setCellValue('A'.$rowIndex, $row['employee']->name);
            $sheet->setCellValue('B'.$rowIndex, $row['employee']->employee_code);
            $sheet->setCellValue('C'.$rowIndex, $row['employee']->department ?? '');
            $sheet->setCellValue('D'.$rowIndex, Carbon::parse($row['date'])->format('Y-m-d'));
            $sheet->setCellValue('E'.$rowIndex, $row['time_in']?->format('H:i:s') ?? '');
            $sheet->setCellValue('F'.$rowIndex, $row['time_out']?->format('H:i:s') ?? '');
            $sheet->setCellValue('G'.$rowIndex, $row['hours'] !== null ? number_format($row['hours'], 2) : '');
            $sheet->setCellValue('H'.$rowIndex, $row['status']);
            $sheet->setCellValue('I'.$rowIndex, $row['is_late'] ? 'Yes' : 'No');
            $sheet->setCellValue('J'.$rowIndex, $row['is_early_clock_out'] ? 'Yes' : 'No');
            $sheet->setCellValue('K'.$rowIndex, $row['late_reason'] ?? '');
            $sheet->setCellValue('L'.$rowIndex, $row['late_reason_text'] ?? '');
            $sheet->setCellValue('M'.$rowIndex, $row['early_reason'] ?? '');
            $sheet->setCellValue('N'.$rowIndex, $row['early_reason_text'] ?? '');
            $rowIndex++;
        }

        $sheet->setCellValue('A'.$rowIndex, 'Totals');
        $sheet->getStyle('A'.$rowIndex)->getFont()->setBold(true);
        $sheet->setCellValue('H'.$rowIndex, "Records: {$this->countRecords($rows)} | Late: {$this->countLate($rows)} | Early: {$this->countEarly($rows)}");

        foreach (['A', 'B', 'D', 'E', 'F', 'G', 'I', 'J'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(14);
        }
        $sheet->getColumnDimension('C')->setWidth(16);
        foreach (['H', 'K', 'L', 'M', 'N'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(24);
        }

        $fileName = 'attendance-detailed-report-'.$from->format('Y-m-d').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function detailedCsvDownload(array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Employee Name',
                'Employee ID',
                'Department',
                'Date',
                'Clock-In Time',
                'Clock-Out Time',
                'Hours Worked',
                'Attendance Status',
                'Late Arrival',
                'Early Clock-Out',
                'Selected Reason (Late)',
                'Custom Reason (Late)',
                'Selected Reason (Early)',
                'Custom Reason (Early)',
            ]);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row['employee']->name,
                    $row['employee']->employee_code,
                    $row['employee']->department ?? '',
                    $row['date'],
                    $row['time_in']?->format('H:i:s') ?? '',
                    $row['time_out']?->format('H:i:s') ?? '',
                    $row['hours'] !== null ? number_format($row['hours'], 2) : '',
                    $row['status'],
                    $row['is_late'] ? 'Yes' : 'No',
                    $row['is_early_clock_out'] ? 'Yes' : 'No',
                    $row['late_reason'] ?? '',
                    $row['late_reason_text'] ?? '',
                    $row['early_reason'] ?? '',
                    $row['early_reason_text'] ?? '',
                ]);
            }

            fclose($out);
        }, 'attendance-detailed-report-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function countRecords(array $rows): int
    {
        return count($rows);
    }

    private function countLate(array $rows): int
    {
        return count(array_filter($rows, fn ($row) => $row['is_late']));
    }

    private function countEarly(array $rows): int
    {
        return count(array_filter($rows, fn ($row) => $row['is_early_clock_out']));
    }

    private function resolveFortnightStart(Request $request): Carbon
    {
        $start = $request->filled('start')
            ? Carbon::parse($request->query('start'))->startOfDay()
            : today();

        return $start->copy()->startOfWeek(Carbon::MONDAY);
    }

    private function workingDays(Carbon $start): array
    {
        return collect(range(0, 13))
            ->map(fn (int $i) => $start->copy()->addDays($i))
            ->filter(fn (Carbon $day) => $day->isWeekday())
            ->values()
            ->all();
    }

    private function buildRows(Request $request, Carbon $start): array
    {
        $end = $start->copy()->addDays(13)->endOfDay();
        $workingDays = $this->workingDays($start);

        $employees = Employee::query()->with('homeLocation')->where('active', true);
        $logs = AttendanceLog::query()->whereBetween('scanned_at', [$start->copy()->startOfDay(), $end]);

        $user = $request->user();

        if (! $user->isAdmin()) {
            $employees->where('home_location_id', $user->location_id);
            $logs->where('location_id', $user->location_id);
        } elseif ($request->filled('location_id')) {
            $employees->where('home_location_id', $request->integer('location_id'));
            $logs->where('location_id', $request->integer('location_id'));
        }

        $employees = $employees->orderBy('name')->get();
        $logs = $logs->orderBy('scanned_at')->get()->groupBy('employee_id');

        $rows = [];
        $totals = array_fill(0, count($workingDays), 0.0);

        foreach ($employees as $employee) {
            $row = [
                'employee' => $employee,
                'days' => [],
                'total_hours' => 0.0,
                'days_worked' => 0,
            ];

            foreach ($workingDays as $i => $day) {
                $dayLogs = $logs->get($employee->id, collect())
                    ->filter(fn (AttendanceLog $log) => $log->scanned_at->toDateString() === $day->toDateString())
                    ->values();

                $timeIn = $dayLogs->firstWhere('type', 'time_in');
                $timeOut = $dayLogs->where('type', 'time_out')->last();

                $hours = 0.0;
                if ($timeIn && $timeOut && $timeOut->scanned_at->gt($timeIn->scanned_at)) {
                    $hours = round($timeIn->scanned_at->diffInMinutes($timeOut->scanned_at) / 60, 2);
                }

                $row['days'][$i] = [
                    'date' => $day,
                    'hours' => $hours,
                    'present' => $timeIn !== null,
                ];
                $row['total_hours'] = round($row['total_hours'] + $hours, 2);
                $totals[$i] = round($totals[$i] + $hours, 2);

                if ($timeIn !== null) {
                    $row['days_worked']++;
                }
            }

            $rows[] = $row;
        }

        return [
            'workingDays' => $workingDays,
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    private function availableLocations(Request $request)
    {
        $user = $request->user();

        return $user->isAdmin()
            ? Location::orderBy('name')->get()
            : Location::where('id', $user->location_id)->get();
    }

    private function excelDownload(Carbon $start, array $workingDays, array $rows, array $totals): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance Report');

        $end = $start->copy()->addDays(13);

        $sheet->setCellValue('A1', 'Attendance Report - '.$start->format('F d, Y').' to '.$end->format('F d, Y'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
        $headers = ['Employee', 'Code'];
        foreach ($workingDays as $i => $day) {
            $week = $i < 5 ? 1 : 2;
            $headers[] = 'W'.$week.' '.$weekdays[$i % 5].' '.$day->format('m/d');
        }
        $headers[] = 'Fortnight Hours';
        $headers[] = 'Days Worked ('.self::WORKING_DAYS.')';

        $headerRow = 3;
        foreach ($headers as $col => $header) {
            $coord = Coordinate::stringFromColumnIndex($col + 1).$headerRow;
            $sheet->setCellValue($coord, $header);
            $sheet->getStyle($coord)->getFont()->setBold(true);
            $sheet->getStyle($coord)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E2B47');
            $sheet->getStyle($coord)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($coord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $rowIndex = $headerRow + 1;
        foreach ($rows as $row) {
            $sheet->setCellValue('A'.$rowIndex, $row['employee']->name);
            $sheet->setCellValue('B'.$rowIndex, $row['employee']->employee_code);

            foreach ($row['days'] as $i => $day) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 3).$rowIndex, $day['present'] ? $day['hours'] : '');
            }

            $sheet->setCellValue(Coordinate::stringFromColumnIndex(count($headers) - 1).$rowIndex, $row['total_hours']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(count($headers)).$rowIndex, $row['days_worked'].' / '.self::WORKING_DAYS);

            $rowIndex++;
        }

        $sheet->setCellValue('A'.$rowIndex, 'Totals');
        $sheet->getStyle('A'.$rowIndex)->getFont()->setBold(true);

        foreach ($totals as $i => $total) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 3).$rowIndex, $total);
        }
        $totalsCoord = Coordinate::stringFromColumnIndex(count($headers) - 1).$rowIndex;
        $sheet->setCellValue($totalsCoord, round(array_sum($totals), 2));
        $sheet->getStyle($totalsCoord)->getFont()->setBold(true);

        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(12);
        for ($i = 3; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(12);
        }

        $fileName = 'attendance-report-'.$start->format('Y-m-d').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
