<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('zirak:admin {email} {--password= : Leave empty to generate one} {--name=Admin}')]
#[Description('Create or update an admin account for the /admin panel')]
class MakeAdmin extends Command
{
    public function handle(): int
    {
        $password = $this->option('password') ?: Str::password(16, symbols: false);

        $user = User::firstOrNew(['email' => $this->argument('email')]);
        $user->fill(['name' => $this->option('name'), 'password' => $password]);
        $user->role = UserRole::Admin;
        $user->email_verified_at ??= now();
        $user->save();

        $this->info('Admin ready: '.$user->email);
        if (! $this->option('password')) {
            $this->line('Password: '.$password);
        }

        return self::SUCCESS;
    }
}
