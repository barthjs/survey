<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SurveyLinkNotification extends Notification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    protected string $surveyId;

    protected string $email;

    protected string $link;

    public function __construct(string $surveyId, string $email)
    {
        $this->surveyId = $surveyId;
        $this->email = $email;
        $this->link = route('surveys.submit', ['id' => $this->surveyId]);
    }

    public function uniqueId(): string
    {
        return 'link_sent_'.$this->surveyId.'_'.$this->email;
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
        return (new MailMessage)
            ->subject(__('Your Survey Link'))
            ->line(__('You have received a link to a survey.'))
            ->action(__('View Survey'), $this->link)
            ->line(__('Thank you for participating!'));
    }
}
