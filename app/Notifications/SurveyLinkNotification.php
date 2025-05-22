<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Survey;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SurveyLinkNotification extends Notification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    protected Survey $survey;

    protected string $email;

    protected string $link;

    public function __construct(Survey $survey, string $email)
    {
        $this->survey = $survey;
        $this->email = $email;
        $this->link = route('surveys.submit', ['id' => $survey->id]);
    }

    public function uniqueId(): string
    {
        return 'link_sent_'.$this->survey->id.'_'.$this->email;
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
            ->greeting(__('Hello!'))
            ->line(__('You have received a link to a survey.'))
            ->action(__('View Survey'), $this->link)
            ->line(__('Thank you for participating!'));
    }
}
