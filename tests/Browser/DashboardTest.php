<?php

declare(strict_types=1);


it('can view dashboard', function () {
    $user = createBrowserUser();

    $this->actingAs($user);

    $page = visit(route('dashboard'));

    $page->assertSee('Dashboard');
});

it('can interact with onboarding dialog', function () {
    $user = createBrowserUser();

    $this->actingAs($user);

    $page = visit(route('dashboard'))
        ->click('[data-test="skip-onboarding"]')
        ->assertSee('Dashboard');
});

it('can see metrics', function () {
    $user = createBrowserUser();

    $this->actingAs($user);

    $page = visit(route('dashboard'));

    $page->assertSee('Dashboard');
});
