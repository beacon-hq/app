<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

afterEach(fn () => $this->browse(function (Browser $browser) {
    $browser->logout();
}));

test('shows welcome screen', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
                ->assertSee('Beacon')
                ->assertDontSee('$');
    });
});
