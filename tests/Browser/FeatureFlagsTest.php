<?php

declare(strict_types=1);

use App\Actions\FeatureFlags\CreateFeatureFlag;
use App\Actions\FeatureTypes\CreateFeatureType;
use App\Values\FeatureFlag;
use App\Values\FeatureType;
use Tests\Unit\Actions\AppContext\SetAppContext;

it('can create new feature flag', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    visit(route('feature-flags.index'))
        ->resize(3024 / 2, 700)
        ->assertSee('Feature Flags')
        ->resize(3024 / 2, 500)
        ->screenshotElement('[data-test="main"]', 'feature-flags-initial')
        ->resize(3024 / 2, 700)
        ->click('[data-test="button-new-feature-flag"]')
        ->assertSee('New Feature Flag')
        ->resize(3024 / 2, 800)
        ->screenshotElement('[data-test="main"]', 'feature-flags-form-create')
        ->type('[data-test="input-feature-flag-name"]', 'demo-feature-flag')
        ->type('[data-test="input-feature-flag-description"]', 'This is a demo feature flag.')
        ->click('[data-test="select-feature-type"]')
        ->click('[data-test="select-option-feature-type-1"]')
        ->screenshotElement('[data-test="main"]', 'feature-flags-form-create-fill')
        ->click('[data-test="button-feature-flag-submit"]')
        ->assertSee('demo-feature-flag')
        ->screenshotElement('[data-test="main"]', 'feature-flags-after-create');
});

it('can edit feature flag basic properties', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    $type = CreateFeatureType::run(FeatureType::factory()->make());
    CreateFeatureFlag::run(FeatureFlag::factory(['featureType' => $type])->make());

    visit(route('feature-flags.index'))
        ->resize(3024 / 2, 1000)
        ->assertSee('Feature Flags')
        ->click('[data-test="button-feature-flag-edit"]')
        ->assertSee('Overview')
        ->resize(3024 / 2, 775)
        ->screenshotElement('[data-test="main"]', 'feature-flags-edit-overview')
        ->resize(3024 / 2, 1000)
        ->click('[data-test="tab-edit"]')
        ->assertSee('Edit Feature Flag')
        ->screenshotElement('[data-test="main"]', 'feature-flags-edit-tab')
        ->click('[data-test="switch-feature-flag-enabled"]')
        ->type('[data-test="input-feature-flag-description"]', 'Updated description for testing.')
        ->screenshotElement('[data-test="main"]', 'feature-flags-edit-form-filled')
        ->click('[data-test="button-feature-flag-submit"]')
        ->assertSee('Updated description for testing.')
        ->assertSee('Enabled')
        ->screenshotElement('[data-test="main"]', 'feature-flags-after-edit');
});

it('can navigate feature flag tabs', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    $type = CreateFeatureType::run(FeatureType::factory()->make());
    CreateFeatureFlag::run(FeatureFlag::factory(['featureType' => $type])->make());

    visit(route('feature-flags.index'))
        ->resize(3024 / 2, 700)
        ->assertSee('Feature Flags')
        ->click('[data-test="button-feature-flag-edit"]')
        ->assertSee('Overview')
        ->screenshotElement('[data-test="main"]', 'feature-flags-overview-tab')
        ->click('[data-test="tab-metrics"]')
        ->assertSee('Application')
        ->assertSee('Environment')
        ->screenshotElement('[data-test="main"]', 'feature-flags-metrics-tab')
        ->click('[data-test="tab-activity"]')
        ->assertSee('Activity Log')
        ->screenshotElement('[data-test="main"]', 'feature-flags-activity-tab');
});

it('can create a basic configuration', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    $type = CreateFeatureType::run(FeatureType::factory()->make());
    CreateFeatureFlag::run(FeatureFlag::factory(['featureType' => $type])->make());

    visit(route('applications.index'))
        ->resize(3024 / 2, 700)
        ->assertSee('Applications')
        ->click('[data-test="button-new-application"]')
        ->type('#name', 'Demo Application')
        ->type('#description', 'This is a demo application.')
        ->type('#display_name', 'Demo App')
        ->click('div.bg-lime-400')
        ->click('[data-test="button-application-submit"]')
        ->assertSee('Demo Application');

    visit(route('environments.index'))
        ->click('[data-test="button-create-environment"]')
        ->type('[data-test="input-environment-name"]', 'Demo')
        ->type('[data-test="input-environment-description"]', 'This is a demo environment')
        ->click('div.bg-sky-400')
        ->click('[data-test="submit-environment"]')
        ->assertSee('Demo');

    visit(route('feature-flags.index'))
        ->assertSee('Feature Flags')
        ->click('[data-test="button-feature-flag-edit"]')
        ->assertSee('Overview')
        ->screenshotElement('[data-test="main"]', 'feature-flags-overview-tab')
        ->click('[data-test="button-add-policy"]')
        ->assertSee('Configuration')
        ->screenshotElement('[data-test="main"]', 'feature-flags-configuration-tab')
        ->click('[data-test="select-application"]')
        ->click('[data-test="select-option-application-0"]')
        ->click('[data-test="select-environment"]')
        ->click('[data-test="select-option-environment-0"]')
        ->click('[data-test="switch-enabled"]')
        ->click('[data-test="button-feature-flag-save"]')
        ->assertSee('Enabled')
        ->assertSee('Demo Application')
        ->assertSee('Demo');
});

it('can configure feature flag rollouts', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    $type = CreateFeatureType::run(FeatureType::factory()->make());
    CreateFeatureFlag::run(FeatureFlag::factory(['featureType' => $type])->make());

    visit(route('feature-flags.index'))->resize(3024 / 2, 900)
        ->assertSee('Feature Flags')
        ->click('[data-test="button-feature-flag-edit"]')
        ->assertSee('Overview')
        ->click('[data-test="tab-configuration"]')
        ->click('[data-test="button-add-application"]')
        ->click(':nth-match([data-test="tab-conditions"], 1)')
        ->screenshotElement('[data-test="main"]', 'feature-flags-conditions-tab')
        ->click(':nth-match([data-test="button-add-conditions"], 1)')
        ->screenshotElement(':nth-match([data-test="card-feature-flag-status"], 1)', 'feature-flags-after-add-conditions')
        ->click(':nth-match([data-test="tab-rollout"], 1)')
        ->assertSee('Rollout Percentage')
        ->screenshotElement('[data-test="main"]', 'feature-flags-rollout-tab')
        ->press('[data-test="slider-rollout-percentage"]')
        ->click('[data-test="select-rollout-strategy"]')
        ->click('[data-test="select-option-rollout-strategy-sticky"]')
        ->type('[data-test="input-multi-input-value"]', 'user.id')
        ->screenshotElement(':nth-match([data-test="card-feature-flag-status"], 1)', 'feature-flags-rollout-configured')
        ->click('[data-test="button-feature-flag-save"]')
        ->screenshotElement('[data-test="main"]', 'feature-flags-rollout-saved');
});

it('can configure feature flag variants', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    $type = CreateFeatureType::run(FeatureType::factory()->make());
    CreateFeatureFlag::run(FeatureFlag::factory(['featureType' => $type])->make());

    visit(route('feature-flags.index'))
        ->resize(3024 / 2, 900)
        ->assertSee('Feature Flags')
        ->click('[data-test="button-feature-flag-edit"]')
        ->assertSee('Overview')
        ->click('[data-test="tab-configuration"]')
        ->click('[data-test="button-add-application"]')
        ->click(':nth-match([data-test="tab-conditions"], 1)')
        ->screenshotElement('[data-test="main"]', 'feature-flags-conditions-tab')
        ->click(':nth-match([data-test="button-add-conditions"], 1)')
        ->screenshotElement(':nth-match([data-test="card-feature-flag-status"], 1)', 'feature-flags-after-add-conditions')
        ->click(':nth-match([data-test="tab-variants"], 1)')
        ->assertSee('Add Variants')
        ->screenshotElement('[data-test="main"]', 'feature-flags-variants-tab')
        ->click('[data-test="button-feature-flags-add-variants"]')
        ->assertSee('Percentage')
        ->click('[data-test="button-feature-flags-add-variant"]')
        ->click(':nth-match([data-test="select-variant-type"], 1)')
        ->click('[data-test="select-option-variant-type-string"]')
        ->type('[data-test="input-variant-value-0"]', 'red')
        ->type('[data-test="input-variant-value-1"]', 'green')
        ->type('[data-test="input-variant-value-2"]', 'blue')
        ->click('[data-test="button-distribute-evenly"]')
        ->screenshotElement('[data-test="button-distribute-evenly"]', 'feature-flags-distribute-evenly')
        ->resize(3024 / 2, 900)
        ->screenshotElement(':nth-match([data-test="card-feature-flag-status"], 1)', 'feature-flags-variants-configured')
        ->click('[data-test="button-feature-flag-save"]')
        ->screenshotElement('[data-test="main"]', 'feature-flags-variants-saved');
});

it('can view feature flags list', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    visit(route('feature-flags.index'))
        ->resize(3024 / 2, 1000)
        ->assertSee('Feature Flags')
        ->screenshotElement('[data-test="table-feature-flags"]', 'feature-flags-list');
});

it('adds a value with Shift+Enter', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    $type = CreateFeatureType::run(FeatureType::factory()->make());
    CreateFeatureFlag::run(FeatureFlag::factory(['featureType' => $type])->make());

    $page = visit(route('feature-flags.index'))
        ->resize(1200, 900)
        ->click('[data-test="button-feature-flag-edit"]')
        ->click('[data-test="tab-configuration"]')
        ->click('[data-test="button-add-application"]')
        ->click(':nth-match([data-test="tab-conditions"], 1)')
        ->click(':nth-match([data-test="button-add-conditions"], 1)')
        ->click('[data-test="select-policies-definition-operator-0"]')
        ->click('[data-test="select-option-policies-definition-operator-0-is one of"]')
        ->type('[data-test="textarea-multi-input-value"]', 'alpha');

    $page->script('document.querySelector("[data-test=\\"textarea-multi-input-value\\"]").dispatchEvent(new KeyboardEvent("keydown", {key: "Enter", shiftKey: true, bubbles: true}));');

    $page->wait(1)->assertSee('alpha');
});
