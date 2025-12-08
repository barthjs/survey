<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

final class VerifyNewEmailNotification extends Notification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    protected string $newEmail;

    protected string $userId;

    public function __construct(string $userId, string $newEmail)
    {
        $this->userId = $userId;
        $this->newEmail = $newEmail;
    }

    public function uniqueId(): string
    {
        return 'new_email_verification_'.$this->userId.'_'.$this->newEmail;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'verification.verify-new-email',
            Carbon::now()->addMinutes(60),
            [
                'id' => $this->userId,
                'hash' => sha1($this->newEmail.$this->userId.config('app.key')),
            ]
        );

        return (new MailMessage)
            ->subject(Lang::get('Verify New Email Address'))
            ->line(Lang::get('Please click the button below to verify your new email address.'))
            ->action(Lang::get('Verify Email Address'), $url);
    }
}
