<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class SeedSingleUser extends Command
{
    protected $signature = 'app:seed-single-user 
                            {--email=admin@qris.local : User email}
                            {--password= : User password (prompted if not provided)}
                            {--name=Admin : User name}
                            {--token : Generate Sanctum token}';

    protected $description = 'Seed single user for the personal QRIS app and optionally generate API token';

    public function handle(): int
    {
        $email = $this->option('email');
        $name = $this->option('name');

        $password = $this->option('password');
        if (! $password) {
            $password = $this->secret('Enter password for user');
        }

        if (! $password) {
            $this->error('Password is required.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'name' => $name,
                'password' => Hash::make($password),
            ]);
            $this->info("User updated: {$email}");
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $this->info("User created: {$email}");
        }

        if ($this->option('token')) {
            $token = $user->createToken('android-app', ['*'])->plainTextToken;
            $this->info('Sanctum token generated:');
            $this->line($token);
            $this->warn('Save this token — it will not be shown again.');
        }

        return self::SUCCESS;
    }
}
