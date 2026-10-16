<?php

declare(strict_types=1);

use App\Livewire\Pages\Survey\ViewSurvey;
use App\Models\Survey;
use App\Models\User;
use App\Notifications\SurveyLinkNotification;
use Livewire\Livewire;

it('renders successfully', function (): void {
    $user = User::factory()->create();
    $survey = Survey::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(ViewSurvey::class, ['id' => $survey->id])
        ->assertStatus(200);
});

it('can share a survey', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $survey = Survey::factory()->create(['user_id' => $user->id]);
    $email = 'test@example.com';

    Livewire::actingAs($user)
        ->test(ViewSurvey::class, ['id' => $survey->id])
        ->set('email', $email)
        ->call('sendEmail');

    Notification::assertSentOnDemand(SurveyLinkNotification::class, fn ($notification, $channels, $notifiable): bool => $notification->uniqueId() === 'link_sent_'.$survey->id.'_'.$email
        && in_array('mail', $channels)
        && $notifiable->routeNotificationFor('mail') === $email);
});
