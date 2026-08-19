<?php

namespace Database\Seeders;

use App\Models\AttendanceLog;
use App\Models\AttendanceReason;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Employee::exists()) {
            $this->command->info('Employees already exist — skipping demo data.');

            return;
        }

        $locations = $this->createLocations();
        $employees = $this->createEmployees($locations);
        $this->generateHistory($employees, $locations);

        $this->command->info('Demo data created: '.count($locations).' locations, '.count($employees).' employees.');
    }

    private function createLocations(): array
    {
        $data = [
            ['name' => 'Honiara Head Office', 'address' => 'Mendana Avenue, Honiara, Solomon Islands', 'timezone' => 'Pacific/Guadalcanal'],
            ['name' => 'Henderson Branch', 'address' => 'Henderson Airport Road, Guadalcanal, Solomon Islands', 'timezone' => 'Pacific/Guadalcanal'],
        ];

        $locations = [];

        foreach ($data as $row) {
            $locations[] = Location::create($row);
        }

        return $locations;
    }

    private function createEmployees(array $locations): array
    {
        $definitions = [
            ['name' => 'Alice Maekana', 'department' => 'Administration', 'gender' => 'women', 'photoIdx' => 44],
            ['name' => 'John Saetana', 'department' => 'ICT', 'gender' => 'men', 'photoIdx' => 32],
            ['name' => 'Marylyn Pugewa', 'department' => 'Finance', 'gender' => 'women', 'photoIdx' => 68],
            ['name' => 'David Aulua', 'department' => 'Operations', 'gender' => 'men', 'photoIdx' => 52],
            ['name' => 'Grace Taupongi', 'department' => 'Human Resources', 'gender' => 'women', 'photoIdx' => 57],
            ['name' => 'Samuel Hunia', 'department' => 'Customer Service', 'gender' => 'men', 'photoIdx' => 75],
            ['name' => 'Ruth Tuke', 'department' => 'Administration', 'gender' => 'women', 'photoIdx' => 12],
            ['name' => 'Peter Rarua', 'department' => 'ICT', 'gender' => 'men', 'photoIdx' => 85],
            ['name' => 'Lucy Manehau', 'department' => 'Operations', 'gender' => 'women', 'photoIdx' => 25],
            ['name' => 'Joseph Ini', 'department' => 'Finance', 'gender' => 'men', 'photoIdx' => 18],
        ];

        $employees = [];

        foreach ($definitions as $i => $definition) {
            $code = str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);
            $slug = strtolower(str_replace(' ', '.', $definition['name']));
            $photo = $this->downloadPhoto($code, $definition['gender'], $definition['photoIdx']);

            $employees[] = Employee::create([
                'employee_code' => 'EMP'.$code,
                'name' => $definition['name'],
                'email' => $slug.'@attendance.local',
                'department' => $definition['department'],
                'photo' => $photo,
                'pin_hash' => Hash::make('1234'),
                'home_location_id' => $locations[$i % 2]->id,
                'active' => true,
            ]);
        }

        return $employees;
    }

    private function downloadPhoto(string $code, string $gender, int $idx): ?string
    {
        $relativePath = "photos/emp_{$code}.jpg";

        if (Storage::disk('public')->exists($relativePath)) {
            return $relativePath;
        }

        try {
            $response = Http::timeout(15)->get("https://randomuser.me/api/portraits/{$gender}/{$idx}.jpg");

            if ($response->successful()) {
                Storage::disk('public')->put($relativePath, $response->body());

                return $relativePath;
            }
        } catch (\Throwable $e) {
            $this->command->warn("Could not download photo for EMP{$code}: {$e->getMessage()}");
        }

        return null;
    }

    private function generateHistory(array $employees, array $locations): void
    {
        $lateReasons = AttendanceReason::active()->forCategory(AttendanceReason::CATEGORY_LATE)->get();
        $earlyReasons = AttendanceReason::active()->forCategory(AttendanceReason::CATEGORY_EARLY)->get();

        $start = today()->subDays(34)->startOfDay();

        for ($day = $start->copy(); $day->lte(today()); $day->addDay()) {
            if (! $day->isWeekday()) {
                continue;
            }

            $isToday = $day->isToday();

            foreach ($employees as $index => $employee) {
                if (! $isToday && random_int(1, 100) <= 15) {
                    continue;
                }

                if ($isToday && $index >= 7 && random_int(1, 100) <= 50) {
                    continue;
                }

                $location = $locations[$index % 2];
                $timeIn = $this->randomTimeIn($day);

                $logIn = AttendanceLog::create([
                    'employee_id' => $employee->id,
                    'location_id' => $location->id,
                    'type' => 'time_in',
                    'scanned_at' => $timeIn,
                    'clock_out_type' => null,
                    'is_late' => $timeIn->gt(Carbon::parse($day->toDateString().' 09:00:00')),
                    'is_early_clock_out' => false,
                ]);

                if ($logIn->is_late && $lateReasons->isNotEmpty() && random_int(1, 100) <= 80) {
                    $reason = $lateReasons->random();
                    $logIn->update([
                        'late_reason_id' => $reason->id,
                        'reason_submitted_at' => $timeIn,
                        'reason_status' => random_int(0, 1) ? AttendanceLog::REASON_APPROVED : AttendanceLog::REASON_PENDING,
                    ]);
                    $this->auditReason($employee, $logIn, 'Late arrival');
                }

                if ($isToday) {
                    continue;
                }

                $timeOut = $this->randomTimeOut($day, $timeIn);
                $isEarly = $timeOut->lt(Carbon::parse($day->toDateString().' 17:00:00'));
                $automatic = random_int(1, 100) <= 10;

                $logOut = AttendanceLog::create([
                    'employee_id' => $employee->id,
                    'location_id' => $location->id,
                    'type' => 'time_out',
                    'scanned_at' => $timeOut,
                    'clock_out_type' => $automatic ? 'automatic' : 'manual',
                    'auto_clocked_out_at' => $automatic ? $timeOut : null,
                    'is_late' => false,
                    'is_early_clock_out' => $isEarly,
                ]);

                if ($isEarly && $earlyReasons->isNotEmpty() && random_int(1, 100) <= 70) {
                    $reason = $earlyReasons->random();
                    $logOut->update([
                        'early_clock_out_reason_id' => $reason->id,
                        'reason_submitted_at' => $timeOut,
                        'reason_status' => random_int(0, 1) ? AttendanceLog::REASON_APPROVED : AttendanceLog::REASON_PENDING,
                    ]);
                    $this->auditReason($employee, $logOut, 'Early clock-out');
                }
            }
        }
    }

    private function randomTimeIn(Carbon $day): Carbon
    {
        return $day->copy()->setTime(7, 45, 0)->addMinutes(random_int(0, 100));
    }

    private function randomTimeOut(Carbon $day, Carbon $timeIn): Carbon
    {
        $base = $day->copy()->setTime(15, 30, 0);
        $timeOut = $base->addMinutes(random_int(0, 150));

        if ($timeOut->lte($timeIn)) {
            return $timeIn->copy()->addHours(8);
        }

        return $timeOut;
    }

    private function auditReason(Employee $employee, AttendanceLog $log, string $label): void
    {
        AuditLog::create([
            'employee_id' => $employee->id,
            'action' => 'attendance_reason_submitted',
            'description' => "{$label} reason submitted by {$employee->name} ({$employee->employee_code}).",
            'metadata' => [
                'attendance_log_id' => $log->id,
                'type' => $log->type,
                'submitted_at' => $log->reason_submitted_at?->toDateTimeString(),
            ],
            'created_at' => $log->reason_submitted_at ?? $log->scanned_at,
        ]);
    }
}
