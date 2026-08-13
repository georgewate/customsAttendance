<?php

namespace Database\Factories;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceLog>
 */
class AttendanceLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'location_id' => Location::factory(),
            'type' => 'time_in',
            'scanned_at' => now(),
        ];
    }
}
