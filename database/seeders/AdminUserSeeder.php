<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('ADMIN_SEED_PASSWORD');
        $generated = false;

        if (! $password) {
            $password = Str::random(12);
            $generated = true;
        }

        $user = User::updateOrCreate([
            'username' => 'admin',
        ], [
            'name' => 'System Administrator',
            'email' => 'admin@bac.local',
            'employee_id' => 'ADM-0001',
            'position_title' => 'IT Administrator',
            'office' => 'BAC Office',
            'account_status' => 'active',
            'password' => Hash::make($password),
        ]);

        if ($generated && $this->command) {
            $this->command->info("Admin user created with username 'admin' and generated password: {$password}");
            $this->command->warn('Please store this password securely and change it in production.');
        }
    }
}
