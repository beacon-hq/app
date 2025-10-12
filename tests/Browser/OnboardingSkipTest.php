<?php

declare(strict_types=1);

use Tests\Unit\Actions\AppContext\SetAppContext;

it('skips onboarding', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);


    $page = visit(route('dashboard'));

    $page
        ->assertSee('Get Started')
        ->click('[data-test="skip-onboarding"]')
        ->refresh()
        ->assertDontSee('Get Started');
});
