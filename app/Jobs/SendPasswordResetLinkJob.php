<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Password;

final class SendPasswordResetLinkJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * @param  array{email: string}  $credentials
     */
    public function __construct(
        private readonly array $credentials,
        private readonly string $locale
    ) {}

    public function uniqueId(): string
    {
        return mb_strtolower($this->credentials['email']);
    }

    public function handle(): void
    {
        App::setLocale($this->locale);
        Password::sendResetLink($this->credentials);
    }
}
