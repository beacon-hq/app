<?php

declare(strict_types=1);

use Tests\Unit\Actions\AppContext\SetAppContext;

it('can create new feature type', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    visit(route('feature-types.index'))
        ->resize(3024 / 2, 700)
        ->assertSee('Feature Types')
        ->screenshotElement('[data-test="main"]', 'feature-types-initial')
        ->resize(3024 / 2, 800)
        ->click('[data-test="button-new-feature-type"]')
        ->assertSee('New Feature Type')
        ->screenshotElement('[data-test="main"]', 'feature-types-form-create')
        ->click('[data-test="icon-picker"]')
        ->type('[data-test="input-icon-picker-search"]', 'micro')
        ->click('[data-test="icon-picker-icon-0"]')
        ->type('[data-test="input-feature-type-name"]', 'Demo Feature Type')
        ->type('[data-test="input-feature-type-description"]', 'This is a demo feature type.')
        ->click('[data-test="sheet-feature-type-form"] div.bg-fuchsia-400')
        ->screenshotElement('[data-test="main"]', 'feature-types-form-create-fill')
        ->click('[data-test="button-feature-type-submit"]')
        ->assertSee('Demo Feature Type')
        ->screenshotElement('[data-test="card-feature-types"]', 'feature-types-after-create');
});

it('can view feature types list', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    $this->actingAs($user);

    visit(route('feature-types.index'))
        ->resize(3024 / 2, 700)
        ->assertSee('Feature Types')
        ->screenshotElement('[data-test="main"]', 'feature-types-list');
});

it('can edit feature types', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    visit(route('feature-types.index'))
        ->resize(3024 / 2, 800)
        ->assertSee('Feature Types')
        ->click('[data-test="button-new-feature-type"]')
        ->assertSee('New Feature Type')
        ->click('[data-test="icon-picker"]')
        ->type('[data-test="input-icon-picker-search"]', 'micro')
        ->click('[data-test="icon-picker-icon-0"]')
        ->type('[data-test="input-feature-type-name"]', 'Edit Me Feature Type')
        ->type('[data-test="input-feature-type-description"]', 'This will be edited.')
        ->click('[data-test="sheet-feature-type-form"] div.bg-sky-400')
        ->click('[data-test="button-feature-type-submit"]')
        ->assertSee('Edit Me Feature Type')
        ->click('tbody tr:first-child a[data-test="button-feature-type-edit"]')
        ->assertSee('Edit Feature Type')
        ->screenshotElement('[data-test="card-feature-types-edit"]', 'feature-types-edit')
        ->type('[data-test="input-feature-type-description"]', 'This is an updated demo feature type.')
        ->screenshotElement('[data-test="main"]', 'feature-types-form-edit-fill')
        ->click('[data-test="button-feature-type-submit"]')
        ->assertSee('Feature Types')
        ->screenshotElement('[data-test="main"]', 'feature-types-after-edit');
});
