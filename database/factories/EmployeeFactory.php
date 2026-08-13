<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_code' => fake()->unique()->numerify('EMP####'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'pin_hash' => Hash::make('1234'),
            'home_location_id' => Location::factory(),
            'active' => true,
        ];
    }

    /**
     * Indicate that the employee is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
