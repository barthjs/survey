<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('renders the homepage page', function (): void {
    get(route('home'))->assertOk();
});

it('renders the thank-you page', function (): void {
    get(route('surveys.thank-you'))->assertOk();
});
