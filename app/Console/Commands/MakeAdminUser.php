<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAdminUser extends Command
{
    protected $signature = 'make:admin
                            {--name= : The admin user\'s name}
                            {--email= : The admin user\'s email}
                            {--password= : The admin user\'s password}';

    protected $description = 'Create an admin user (or promote an existing user to admin)';

    public function handle(): int
    {
        $name = $this->option('name') ?? $this->ask('Name');
        $email = $this->option('email') ?? $this->ask('Email');
        $password = $this->option('password') ?? $this->secret('Password');

        if (! $name || ! $email || ! $password) {
            $this->error('Name, email and password are all required.');
            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update(['role' => 'admin']);
            $this->info("Existing user '{$email}' promoted to admin.");
        } else {
            User::create([
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make($password),
                'role'     => 'admin',
            ]);
            $this->info("Admin user '{$email}' created.");
        }

        return self::SUCCESS;
    }
}
