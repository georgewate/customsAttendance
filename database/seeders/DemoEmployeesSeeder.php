<?php

namespace Database\Seeders;

use App\Models\AttendanceLog;
use App\Models\AttendanceReason;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoEmployeesSeeder extends Seeder
{
    private const DEPARTMENTS = [
        'Security', 'Operations', 'Customs Clearance', 'Administration',
        'IT Support', 'Logistics', 'Finance', 'Human Resources',
    ];

    private const NAMES = [
        'Budi Santoso', 'Siti Rahayu', 'Agus Wijaya', 'Dewi Lestari', 'Rudi Hartono',
        'Sri Wahyuni', 'Andi Pratama', 'Rina Kusuma', 'Joko Susilo', 'Maya Anggraini',
        'Hendra Gunawan', 'Lina Marlina', 'Dedi Kurniawan', 'Nur Aini', 'Fajar Nugroho',
        'Indah Permatasari', 'Rizky Ramadhan', 'Fitri Handayani', 'Bambang Setiawan', 'Ratna Sari',
    ];

    public function run(): void
    {
        if (Employee::count() > 0) {
            $this->command->info('Employees already exist — skipping.');

            return;
        }

        $locations = Location::all();
        $lateReasons = AttendanceReason::where('id', '<=', 10)->pluck('id')->all();
        $earlyReasons = AttendanceReason::where('id', '>', 10)->pluck('id')->all();

        $employees = [];

        foreach (self::NAMES as $i => $name) {
            $pin = str_pad((string) (1000 + $i), 4, '0', STR_PAD_LEFT);

            $employee = Employee::create([
                'employee_code' => 'EMP-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)).'@attendance.local',
                'department' => self::DEPARTMENTS[$i % count(self::DEPARTMENTS)],
                'pin_hash' => Hash::make($pin),
                'home_location_id' => $locations->random()->id,
                'active' => true,
            ]);

            $employees[] = $employee;
        }

        $this->command->info('Created '.count($employees).' employees.');
        $this->command->info('PINs are 1000-1019. Sample: EMP-001 / 1000');

        $days = $this->lastWorkingDays(14);

        foreach ($employees as $employee) {
            $dayOff = $days[array_rand($days)];

            foreach ($days as $day) {
                if ($day === $dayOff && rand(1, 100) <= 40) {
                    continue;
                }

                $this->createDailyLogs($employee, $day, $locations, $lateReasons, $earlyReasons);
            }
        }

        $this->command->info('Attendance logs created for '.count($days).' working days.');
    }

    private function lastWorkingDays(int $count): array
    {
        $days = [];
        $date = now()->startOfDay()->subDay();

        while (count($days) < $count) {
            if (! $date->isWeekend()) {
                $days[] = $date->copy();
            }
            $date->subDay();
        }

        return array_reverse($days);
    }

    private function createDailyLogs(Employee $employee, $day, $locations, array $lateReasons, array $earlyReasons): void
    {
        $location = rand(1, 100) <= 15 ? $locations->random() : $employee->homeLocation;

        $isLate = rand(1, 100) <= 25;
        $timeIn = $day->copy()->setTimeFromTimeString($isLate ? $this->randomTime('09:05', '10:45') : $this->randomTime('07:45', '08:55'));

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => $timeIn,
            'is_late' => $isLate,
            'late_reason_id' => $isLate && rand(1, 100) <= 80 ? $lateReasons[array_rand($lateReasons)] : null,
            'late_reason_text' => null,
            'reason_submitted_at' => $isLate && rand(1, 100) <= 80 ? $timeIn->copy()->addMinutes(rand(2, 30)) : null,
            'reason_status' => $isLate && rand(1, 100) <= 80 ? $this->randomStatus() : null,
        ]);

        if (rand(1, 100) <= 8) {
            return;
        }

        $isEarlyOut = rand(1, 100) <= 18;
        $timeOut = $day->copy()->setTimeFromTimeString($isEarlyOut ? $this->randomTime('15:30', '16:55') : $this->randomTime('17:00', '18:45'));
        $auto = rand(1, 100) <= 10;

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_out',
            'scanned_at' => $timeOut,
            'clock_out_type' => $auto ? 'automatic' : 'manual',
            'auto_clocked_out_at' => $auto ? $timeOut : null,
            'is_early_clock_out' => $isEarlyOut,
            'early_clock_out_reason_id' => $isEarlyOut && rand(1, 100) <= 80 ? $earlyReasons[array_rand($earlyReasons)] : null,
            'early_clock_out_reason_text' => null,
            'reason_submitted_at' => $isEarlyOut && rand(1, 100) <= 80 ? $timeOut->copy()->addMinutes(rand(2, 30)) : null,
            'reason_status' => $isEarlyOut && rand(1, 100) <= 80 ? $this->randomStatus() : null,
        ]);
    }

    private function randomTime(string $from, string $to): string
    {
        [$fromH, $fromM] = array_map('intval', explode(':', $from));
        [$toH, $toM] = array_map('intval', explode(':', $to));

        $fromMin = $fromH * 60 + $fromM;
        $toMin = $toH * 60 + $toM;
        $minute = rand($fromMin, $toMin);

        return sprintf('%02d:%02d', intdiv($minute, 60), $minute % 60);
    }

    private function randomStatus(): string
    {
        $roll = rand(1, 100);

        return $roll <= 70 ? 'approved' : ($roll <= 90 ? 'pending' : 'rejected');
    }
}