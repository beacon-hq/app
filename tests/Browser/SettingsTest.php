<?php

declare(strict_types=1);

it('can view settings', function () {
    [, $user, ] = createOwner();

    $this->actingAs($user);

    $page = visit(route('settings.index'));

    $page->resize(3024 / 2, 600);

    $page->assertSee('Settings')
        ->screenshot(filename: 'settings-initial');
});
