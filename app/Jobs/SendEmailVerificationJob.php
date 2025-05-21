<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEmailVerificationJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    private User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function uniqueId(): string
    {
        return $this->user->email;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->user->hasVerifiedEmail() && config('app.enable_email_verification')) {
            $this->user->sendEmailVerificationNotification();
        }
    }
}
