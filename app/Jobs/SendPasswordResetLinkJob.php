<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Password;

class SendPasswordResetLinkJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    private array $credentials;

    private string $locale;

    /**
     * Create a new job instance.
     */
    public function __construct(array $credentials, string $locale)
    {
        $this->credentials = $credentials;
        $this->locale = $locale;
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
        App::setLocale($this->locale);
        Password::sendResetLink($this->credentials);
    }
}
