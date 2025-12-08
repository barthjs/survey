<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

final class ResetPasswordCommand extends Command
{
    protected $signature = 'app:reset-password {email?}';

    protected $description = 'Reset the password for a user by the email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        if (empty($email)) {
            $email = $this->ask(__('Enter email to reset password'));
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error(__('Invalid email'));

            return 1;
        }

        try {
            $user = User::where('email', $email)->firstOrFail();
        } catch (ModelNotFoundException) {
            $this->error(__('User not found'));

            return 1;
        }

        if (! $this->confirm(__('Resetting password for user: ').$user->name, true)) {
            return 1;
        }

        $password = '';
        while (empty($password)) {
            $password = $this->secret(__('Enter new password for user: ').$user->name);
            if (empty($password)) {
                $this->error(__('Password cannot be empty'));

                continue;
            }

            $confirmPassword = $this->secret(__('Confirm password'));
            if ($password !== $confirmPassword) {
                $this->error(__('Passwords do not match'));
                $password = '';
            }
        }

        try {
            $user->password = Hash::make($password);
            $user->remember_token = null;
            $user->email_verified_at = null;

            DB::table(config('auth.passwords.users.table'))->where('email', $email)->delete();
            DB::table(config('session.table'))->where('user_id', $user->id)->delete();

            $user->save();

            $this->info(__('Password reset successfully'));
        } catch (Exception $e) {
            Log::error(__('Password reset failed for user: ').$email, ['exception' => $e]);
            $this->error(__('An error occurred while resetting the password.'));

            return 1;
        }

        return 0;
    }
}
