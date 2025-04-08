<?php

declare(strict_types=1);

it('renders the homepage page successfully', function () {
    $response = $this->get(route('home'));

    $response->assertStatus(200);
});
