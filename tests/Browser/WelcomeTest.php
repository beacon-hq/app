<?php

declare(strict_types=1);

use Laravel\Pennant\Feature;

it('shows welcome screen', function () {
    Feature::define('billing-enabled', fn () => false);

    visit('/')
        ->assertSee('Feature Flag Management For Laravel')
        ->assertDontSee('Pricing');
});

it('shows billing welcome screen', function () {
    Feature::define('billing-enabled', fn () => true);

    visit('/')
        ->assertSee('Feature Flag Management For Laravel')
        ->assertSee('Pricing');
});
