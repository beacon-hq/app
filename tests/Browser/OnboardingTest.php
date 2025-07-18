<?php

declare(strict_types=1);

use App\Events\FeatureFlagMissedEvent;
use App\Listeners\CreateMissingFeatureFlagListener;
use App\Listeners\RecordFeatureFlagUsage;
use App\Values\FeatureFlag;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;
use Illuminate\Support\Facades\App;
use Laravel\Dusk\Browser;

it('onboards new teams', function () {
    $user = createBrowserUser();

    App::context(organization: $user->organizations()->first(), team: $user->teams()->first());

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 1964 / 2);

        $browser
            ->loginAs($user)
            ->visitRoute('dashboard')
            ->waitForText('Get Started')
            ->screenshotElement('@onboarding-dialog', 'onboarding-1');

        $browser
            ->click('@next-config')
            ->waitForTextIn('@done-install', 'Done')
            ->screenshotElement('@onboarding-dialog', 'onboarding-2');

        $browser
            ->click('@next-integration')
            ->waitForTextIn('@done-config', 'Done')
            ->waitForText('Waiting')
            ->screenshotElement('@onboarding-dialog', 'onboarding-3');

        $event = new FeatureFlagMissedEvent(
            FeatureFlag::withoutValidation(name: 'test-flag'),
            FeatureFlagContext::from(scopeType: 'array', appName: 'Beacon Onboarding', environment: 'production'),
            FeatureFlagResponse::from('test-flag', null, true)
        );
        (app(CreateMissingFeatureFlagListener::class))->handle($event);
        (app(RecordFeatureFlagUsage::class))->handle($event);

        $browser
            ->waitForText('Finish')
            ->screenshotElement('@onboarding-dialog', 'onboarding-4');

        $browser
            ->click('@finish-onboarding')
            ->waitForTextIn('@metric-card-total_flags', '1');

        $app = $browser->element('#app')->getSize();
        $browser->resize($app->getWidth(), $app->getHeight() + 100);

        $browser
            ->refresh()
            ->assertDontSee('Get Started')
            ->screenshotElement('@dashboard', 'dashboard');
    });
});
