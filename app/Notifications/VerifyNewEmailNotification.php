<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class VerifyNewEmailNotification extends Notification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    protected string $newEmail;

    protected User $user;

    public function __construct(User $user, string $newEmail)
    {
        $this->user = $user;
        $this->newEmail = $newEmail;
    }

    public function uniqueId(): string
    {
        return 'new_email_verification_'.$this->user->id.'_'.$this->newEmail;
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
                'id' => $this->user->id,
                'hash' => sha1($this->newEmail.$this->user->id.config('app.key')),
            ]
        );

        return (new MailMessage)
            ->subject(Lang::get('Verify New Email Address'))
            ->line(Lang::get('Please click the button below to verify your new email address.'))
            ->action(Lang::get('Verify Email Address'), $url);
    }
}
