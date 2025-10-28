<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Events\FeatureFlagMissedEvent;
use App\Listeners\CreateMissingFeatureFlagListener;
use App\Listeners\RecordFeatureFlagUsage;
use App\Values\FeatureFlag;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;

it('onboards new teams', function () {
    [$team, $user, $organization] = createOwner();

    $this->actingAs($user);

    SetAppContext::run($organization, $team);

    $page = visit(route('dashboard'))
        ->resize(3024 / 2, 1964 / 2);

    $page
        ->assertSee('Get Started')
        ->screenshotElement('[data-test="onboarding-dialog"]', 'onboarding-1')
        ->click('[data-test="next-config"]')
        ->assertSeeIn('[data-test="done-install"]', 'Done')
        ->screenshotElement('[data-test="onboarding-dialog"]', 'onboarding-2')
        ->click('[data-test="next-integration"]')
        ->assertSeeIn('[data-test="done-config"]', 'Done')
        ->assertSee('Waiting')
        ->screenshotElement('[data-test="onboarding-dialog"]', 'onboarding-3');

    $event = new FeatureFlagMissedEvent(
        FeatureFlag::withoutValidation(name: 'test-flag'),
        FeatureFlagContext::from(scopeType: 'array', appName: 'Beacon Onboarding', environment: 'production'),
        FeatureFlagResponse::from('test-flag', null, true)
    );
    (app(CreateMissingFeatureFlagListener::class))->handle($event);
    (app(RecordFeatureFlagUsage::class))->handle($event);

    $page
        ->assertSee('Finish')
        ->screenshotElement('[data-test="onboarding-dialog"]', 'onboarding-4')
        ->click('[data-test="finish-onboarding"]')
        ->assertSeeIn('[data-test="metric-card-total_flags"]', '1')
        ->screenshotElement('[data-test="main"]', 'dashboard')
        ->navigate(route('dashboard'))
        ->assertDontSee('Get Started');
});
