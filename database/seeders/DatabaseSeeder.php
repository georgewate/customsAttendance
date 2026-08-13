<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (User::where('role', 'admin')->exists()) {
            $this->command->info('An admin user already exists — skipping.');

            return;
        }

        $email = env('ADMIN_EMAIL', 'admin@attendance.local');
        $password = env('ADMIN_PASSWORD') ?: Str::password(16);

        User::create([
            'name' => 'Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
        ]);

        $this->command->info("Admin account created: {$email} / {$password}");
    }
}
