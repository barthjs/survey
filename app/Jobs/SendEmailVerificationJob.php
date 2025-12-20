<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\App;

final class SendEmailVerificationJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly User $user,
        private readonly string $locale
    ) {}

    public function uniqueId(): string
    {
        return $this->user->email;
    }

    public function handle(): void
    {
        if (! $this->user->hasVerifiedEmail() && config()->boolean('app.enable_email_verification')) {
            App::setLocale($this->locale);
            $this->user->sendEmailVerificationNotification();
        }
    }
}
