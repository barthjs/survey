<?php

declare(strict_types=1);

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('surveys.index'))->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('surveys.index'))->assertStatus(200);
});
