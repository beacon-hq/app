<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\ColorPicker;

it('can create new feature flag', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('feature-flags.index')
            ->waitForText('Feature Flags')
            ->resize(3024 / 2, 500)
            ->screenshotElement('@main', 'feature-flags-initial')
            ->resize(3024 / 2, 700);

        $browser
            ->pause(1000)
            ->refresh()
            ->scrollIntoView('@button-new-feature-flag')
            ->click('@button-new-feature-flag')
            ->waitForText('New Feature Flag')
            ->resize(3024 / 2, 800)
            ->screenshotElement('@main', 'feature-flags-form-create');

        $browser
            ->type('@input-feature-flag-name', 'demo-feature-flag')
            ->type('@input-feature-flag-description', 'This is a demo feature flag.')
            ->click('@select-feature-type')
            ->pause(500)
            ->click('@select-option-feature-type-1')
            ->screenshot('feature-flags-form-create-fill')
            ->click('@button-feature-flag-submit')
            ->waitForText('demo-feature-flag')
            ->screenshotElement('@main', 'feature-flags-after-create');
    });
});

it('can edit feature flag basic properties', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 1000);

        $browser
            ->loginAs($user)
            ->visitRoute('feature-flags.index')
            ->waitForText('Feature Flags');

        $browser
            ->click('@button-feature-flag-edit')
            ->waitForText('Overview')
            ->pause(1000)
            ->resize(3024 / 2, 775)
            ->screenshotElement('@main', 'feature-flags-edit-overview')
            ->resize(3024 / 2, 1000);

        $browser
            ->click('@tab-edit')
            ->waitForText('Edit Feature Flag')
            ->screenshotElement('@main', 'feature-flags-edit-tab');

        $browser
            ->click('@switch-feature-flag-enabled')
            ->type('@input-feature-flag-description', 'Updated description for testing.')
            ->screenshot('feature-flags-edit-form-filled')
            ->click('@button-feature-flag-submit');


        $browser
            ->waitForText('Updated description for testing.')
            ->assertSee('Enabled')
            ->screenshotElement('@main', 'feature-flags-after-edit');
    });
});

it('can navigate feature flag tabs', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('feature-flags.index')
            ->waitForText('Feature Flags');

        $browser
            ->click('@button-feature-flag-edit')
            ->waitForText('Overview')
            ->screenshotElement('@main', 'feature-flags-overview-tab');

        $browser
            ->click('@tab-metrics')
            ->waitForText('Application')
            ->assertSee('Environment')
            ->screenshotElement('@main', 'feature-flags-metrics-tab');

        $browser
            ->click('@tab-activity')
            ->waitForText('Activity Log')
            ->pause(1000)
            ->screenshotElement('@main', 'feature-flags-activity-tab');
    });
});

it('can create a basic configuration', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user);

        $browser
            ->visitRoute('applications.index')
            ->waitForText('Applications')
            ->click('@button-new-application')
            ->type('#name', 'Demo Application')
            ->type('#description', 'This is a demo application.')
            ->type('#display_name', 'Demo App')
            ->within(new ColorPicker(), function (Browser $browser) {
                $browser->selectColor('lime');
            })
            ->pause(500)
            ->click('@button-application-submit')
            ->waitForText('Demo Application');

        $browser
            ->visitRoute('environments.index')
            ->click('@button-create-environment')
            ->type('@input-environment-name', 'Demo')
            ->type('@input-environment-description', 'This is a demo environment')
            ->within(new ColorPicker(), function (Browser $browser) {
                $browser->selectColor('sky');
            })
            ->click('@submit-environment')
            ->waitForText('Demo');

        $browser
            ->visitRoute('feature-flags.index')
            ->waitForText('Feature Flags');

        $browser
            ->click('@button-feature-flag-edit')
            ->waitForText('Overview')
            ->screenshotElement('@main', 'feature-flags-overview-tab');

        $browser
            ->click('@button-add-policy')
            ->waitForText('Configuration')
            ->pause(500)
            ->screenshotElement('@main', 'feature-flags-configuration-tab');

        $browser
            ->click('@select-application')
            ->pause(500)
            ->click('@select-option-application-0')
            ->pause(500);

        $browser->click('@select-environment')
            ->pause(500)
            ->click('@select-option-environment-0')
            ->pause(500);

        $browser
            ->click('@switch-enabled')
            ->pause(500);

        $browser
            ->click('@button-feature-flag-save')
            ->waitForText('Enabled')
            ->assertSee('Demo Application')
            ->assertSee('Demo');
    });
});

it('can configure feature flag rollouts and variants', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 900);

        $browser
            ->loginAs($user)
            ->visitRoute('feature-flags.index')
            ->waitForText('Feature Flags');

        $browser
            ->click('@button-feature-flag-edit')
            ->waitForText('Overview');

        if ($browser->element('@button-add-policy')) {
            $browser->click('@button-add-policy');
        }

        $browser
            ->click('@tab-configuration')
            ->waitFor('@button-add-application')
            ->click('@button-add-application')
            ->pause(500);

        $browser
            ->click('@tab-conditions')
            ->screenshotElement('@main', 'feature-flags-conditions-tab');

        if ($browser->element('@button-add-conditions')) {
            $browser
                ->click('@button-add-conditions')
                ->pause(500)
                ->screenshotElement('@card-feature-flag-status', 'feature-flags-after-add-conditions');
        }

        $browser
            ->waitForText('Rollout')
            ->click('@tab-rollout')
            ->waitForText('Rollout Percentage')
            ->resize(3024 / 2, 750)
            ->screenshotElement('@main', 'feature-flags-rollout-tab')
            ->resize(3024 / 2, 900);

        $browser
            ->clickAndHold('@slider-rollout-percentage')
            ->moveMouse(100, 0)
            ->pause(500)
            ->click('@select-rollout-strategy')
            ->pause(500)
            ->click('@select-option-rollout-strategy-sticky')
            ->pause(500)
            ->type('@input-multi-input-value', 'user.id')
            ->keys('@input-multi-input-value', '{enter}')
            ->pause(500)
            ->screenshotElement('@card-feature-flag-status', 'feature-flags-rollout-configured');

        $browser->resize(3024 / 2, 640);

        $browser
            ->click('@tab-variants')
            ->waitForText('Add Variants')
            ->screenshotElement('@main', 'feature-flags-variants-tab');

        $browser
            ->click('@button-feature-flags-add-variants')
            ->waitForText('Percentage')
            ->click('@button-feature-flags-add-variant');


        $browser
            ->click('@select-variant-type')
            ->pause(500)
            ->click('@select-option-variant-type-string');

        $browser
            ->type('@input-variant-value-0', 'red')
            ->type('@input-variant-value-1', 'green')
            ->type('@input-variant-value-2', 'blue');

        $browser
            ->click('@button-distribute-evenly')
            ->screenshotElement('@button-distribute-evenly', 'feature-flags-distribute-evenly')
            ->resize(3024 / 2, 900)
            ->pause(500)
            ->screenshotElement('@card-feature-flag-status', 'feature-flags-variants-configured');

        $browser
            ->click('@button-feature-flag-save')
            ->pause(1000)
            ->screenshotElement('@main', 'feature-flags-configuration-saved');
    });
});

it('can view feature flags list', function () {
    $user = User::where('email', 'support@beacon-hq.dev')->first();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 1000);
        $browser->deleteCookie('beacon_session');

        $browser
            ->loginAs($user)
            ->visitRoute('feature-flags.index')
            ->waitForText('Feature Flags')
            ->screenshotElement('@table-feature-flags', 'feature-flags-list');
    });
});
