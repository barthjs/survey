<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Password;

class SendPasswordResetLinkJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    private array $credentials;

    /**
     * Create a new job instance.
     */
    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    public function uniqueId(): string
    {
        return mb_strtolower($this->credentials['email']);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Password::sendResetLink($this->credentials);
    }
}
