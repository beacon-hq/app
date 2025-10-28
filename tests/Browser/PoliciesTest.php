<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Models\Policy;
use Bag\Collection;

it('can create new policy', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    $page = visit(route('policies.index'))->resize(1512, 700);

    $page
        ->assertSee('Policies')
        ->screenshotElement('[data-test="main"]');

    $page
        ->click('[data-test="button-new-policy"]')
        ->assertSee('New Policy')
        ->screenshotElement('[data-test="main"]');

    $page
        ->type('[data-test="input-policy-name"]', 'Demo Policy')
        ->type('[data-test="input-policy-description"]', 'This is a demo policy.')
        ->screenshot(filename: 'policies-form-create-fill')
        ->click('[data-test="button-policy-submit"]')
        ->assertSee('Demo Policy');

    $page
        ->navigate(route('policies.index'))
        ->assertSee('Policies');

    $page
        ->click('[data-test="button-new-policy"]')
        ->assertSee('New Policy');

    $page
        ->type('[data-test="input-policy-name"]', 'Demo Policy Two')
        ->type('[data-test="input-policy-description"]', 'This is a demo policy.')
        ->click('[data-test="button-policy-submit"]')
        ->assertSee('Demo Policy');
});

it('can view policies list', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    $page = visit(route('policies.index'))->resize(1512, 700);

    $page
        ->assertSee('Policies')
        ->screenshotElement('[data-test="main"]');
});

it('can edit policies', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    Policy::factory()->for($team)->create(['name' => 'Demo Policy']);

    visit(route('policies.index'))
        ->resize(1512, 900)
        ->click('[data-test="button-policies-edit"]')
        ->assertSee('Edit Policy')
        ->wait(1)
        ->screenshotElement('[data-test="main"]')
        ->resize(1512, 900)
        ->type('[data-test="input-policy-description"]', 'This is an updated demo policy.')
        ->screenshot(filename: 'policies-form-edit-fill')
        ->click('[data-test="button-policy-submit"]');
});

it('can edit policy conditions', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    Policy::factory()->for($team)->create(['name' => 'Demo Policy', 'definition' => Collection::empty()]);

    Policy::factory()->for($team)->create(['name' => 'Demo Policy 2']);

    visit(route('policies.index'))
        ->resize(1512, 900)
        ->click(':nth-match([data-test="button-policies-edit"], 1)')
        ->assertSee('Edit Policy')
        ->click('[data-test="button-policies-add-conditions"]')
        ->screenshotElement('[data-test="policies-form-definition"]')
        ->click('[data-test="select-policies-definition-type-0"]')
        ->click('[data-test="select-option-policies-definition-type-0-expression"]')
        ->type('[data-test="input-policies-definition-subject-0"]', 'user.id')
        ->click('[data-test="select-policies-definition-operator-0"]')
        ->click('[data-test="select-option-policies-definition-operator-0-equals"]')
        ->wait(1)
        ->type('[data-test="input-policy-value-0"]', '123')
        ->click('[data-test="main"]')
        ->screenshotElement('[data-test="policies-form-definition"]')
        ->click('[data-test="select-policies-definition-type-0"]')
        ->wait(1)
        ->click('[data-test="select-option-policies-definition-type-0-policy"]')
        ->wait(1)
        ->click('[data-test="select-policies-definition-policy-0"]')
        ->wait(1)
        ->click('[data-test="select-option-policies-definition-policy-0-0"]')
        ->click('[data-test="main"]')
        ->screenshotElement('[data-test="policies-form-definition"]')
        ->click('[data-test="select-policies-definition-type-0"]')
        ->wait(1)
        ->click('[data-test="select-option-policies-definition-type-0-datetime"]')
        ->wait(1)
        ->click('[data-test="select-policies-definition-datetime-0"]')
        ->wait(1)
        ->click('[data-test="select-option-policies-definition-datetime-0-after"]')
        ->click('[data-test="datepicker-policies-definition-datetime-0"]')
        ->wait(1)
        ->click('.rdp-tbody tr:nth-child(5) td:nth-child(5) button')
        ->type('[data-test="timepicker-policies-definition-datetime-0-hour"]', '03')
        ->type('[data-test="timepicker-policies-definition-datetime-0-minute"]', '30')
        ->type('[data-test="timepicker-policies-definition-datetime-0-second"]', '47')
        ->click('[data-test="select-policies-definition-timezone-0"]')
        ->wait(1)
        ->click('[data-test="select-policies-definition-timezone-0-option-5"]')
        ->click('[data-test="main"]')
        ->screenshotElement('[data-test="policies-form-definition"]')
        ->click('[data-test="select-policies-definition-type-0"]')
        ->click('[data-test="select-option-policies-definition-type-0-expression"]')
        ->type('[data-test="input-policies-definition-subject-0"]', 'user.id')
        ->click('[data-test="select-policies-definition-operator-0"]')
        ->click('[data-test="select-option-policies-definition-operator-0-equals"]')
        ->wait(1)
        ->type('[data-test="input-policy-value-0"]', '123')
        ->click('[data-test="main"]')
        ->screenshotElement('[data-test="policies-form-definition"]')
        ->click('[data-test="button-policies-add"]')
        ->wait(1)
        ->click('[data-test="select-policies-definition-type-1"]')
        ->click('[data-test="select-option-policies-definition-type-1-operator"]')
        ->click('[data-test="select-policies-definition-operator-1"]')
        ->click('[data-test="select-option-policies-definition-operator-1-AND"]')
        ->click('[data-test="main"]')
        ->click('[data-test="button-policies-add"]')
        ->wait(1)
        ->screenshotElement('[data-test="policies-form-definition"]')
        ->click('[data-test="select-policies-definition-type-2"]')
        ->wait(1)
        ->click('[data-test="select-option-policies-definition-type-2-datetime"]')
        ->wait(1)
        ->click('[data-test="select-policies-definition-datetime-2"]')
        ->wait(1)
        ->click('[data-test="select-option-policies-definition-datetime-2-after"]')
        ->click('[data-test="datepicker-policies-definition-datetime-2"]')
        ->wait(1)
        ->click('.rdp-tbody tr:nth-child(5) td:nth-child(5) button')
        ->type('[data-test="timepicker-policies-definition-datetime-2-hour"]', '03')
        ->append('[data-test="timepicker-policies-definition-datetime-2-minute"]', '30')
        ->append('[data-test="timepicker-policies-definition-datetime-2-second"]', '47')
        ->click('[data-test="select-policies-definition-timezone-2"]')
        ->wait(1)
        ->click('[data-test="select-policies-definition-timezone-2-option-5"]')
        ->click('[data-test="main"]')
        ->screenshotElement('[data-test="policies-form-definition"]')
        ->click('[data-test="button-policies-add"]')
        ->wait(1)->click('[data-test="select-policies-definition-type-3"]')
        ->click('[data-test="select-option-policies-definition-type-3-operator"]')
        ->click('[data-test="select-policies-definition-operator-3"]')
        ->click('[data-test="select-option-policies-definition-operator-3-AND"]')
        ->click('[data-test="button-policies-add"]')
        ->wait(1)->click('[data-test="select-policies-definition-type-4"]')
        ->wait(1)
        ->click('[data-test="select-option-policies-definition-type-4-policy"]')
        ->wait(1)
        ->click('[data-test="select-policies-definition-policy-4"]')
        ->wait(1)
        ->click('[data-test="select-option-policies-definition-policy-4-0"]')
        ->click('[data-test="main"]')
        ->screenshotElement('[data-test="policies-form-definition"]');
});
