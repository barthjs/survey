<?php

declare(strict_types=1);

use App\Livewire\Pages\Survey\ViewSurvey;
use App\Models\Survey;
use App\Models\User;
use App\Notifications\SurveyLinkNotification;
use Livewire\Livewire;

it('renders successfully', function () {
    $user = User::factory()->create();
    $survey = Survey::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(ViewSurvey::class, ['id' => $survey->id])
        ->assertStatus(200);
});

it('can share a survey', function () {
    Notification::fake();

    $user = User::factory()->create();
    $survey = Survey::factory()->create(['user_id' => $user->id]);
    $email = 'test@example.com';

    Livewire::actingAs($user)
        ->test(ViewSurvey::class, ['id' => $survey->id])
        ->set('email', $email)
        ->call('sendEmail');

    Notification::assertSentOnDemand(SurveyLinkNotification::class, function ($notification, $channels, $notifiable) use ($survey, $email) {
        return $notification->uniqueId() === 'link_sent_'.$survey->id.'_'.$email
            && in_array('mail', $channels)
            && $notifiable->routeNotificationFor('mail') === $email;
    });
});
