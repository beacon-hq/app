<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

test('shows welcome screen', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
                ->assertSee('Beacon')
                ->assertDontSee('$');
    });
});
