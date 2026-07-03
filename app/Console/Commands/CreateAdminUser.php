<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an administrator user interactively';

    public function handle(): int
    {
        $name = $this->ask('Full name');
        $username = $this->ask('Username');
        $email = $this->ask('Email');
        $employeeId = $this->ask('Employee ID');
        $position = $this->ask('Position title');
        $office = $this->ask('Office');

        // Password with confirmation
        $password = $this->secret('Password');
        $passwordConfirm = $this->secret('Confirm Password');

        if ($password !== $passwordConfirm) {
            $this->error('Passwords do not match.');
            return self::FAILURE;
        }

        // Basic uniqueness checks
        if (User::where('username', $username)->exists()) {
            $this->error('The username is already taken.');
            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('The email is already taken.');
            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'employee_id' => $employeeId,
            'position_title' => $position,
            'office' => $office,
            'password' => Hash::make($password),
            'account_status' => 'active',
        ]);

        $this->info("Administrator created: {$username}");
        return self::SUCCESS;
    }
}
