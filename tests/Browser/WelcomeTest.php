<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

it('shows welcome screen', function () {
    Config::set('beacon.billing.enabled', false);

    visit('/')
        ->assertSee('Feature Flag Management For Laravel')
        ->assertDontSee('$');
});
