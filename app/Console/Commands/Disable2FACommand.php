<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

final class Disable2FACommand extends Command
{
    protected $signature = 'app:disable-2fa {email?}';

    protected $description = 'Disable 2FA for a user by email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        if (empty($email)) {
            $email = $this->ask(__('Enter email to disable 2FA'));
        }

        $user = User::where('email', '=', $email)->first();
        if ($user === null) {
            $this->error(__('User not found'));

            return 1;
        }

        if (! $this->confirm(__('Disabling 2FA for user: ').$user->email, true)) {
            return 1;
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_enabled_at' => null,
        ])->save();

        $this->info(__('Two factor authentication disabled.'));

        return 0;
    }
}
