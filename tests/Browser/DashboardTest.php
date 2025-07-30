<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Dusk\Browser;

it('can view dashboard', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('dashboard')
            ->waitForText('Dashboard')
            ->screenshotElement('@main', 'dashboard-initial');

        $browser
            ->assertSee('Dashboard');
    });
});

it('can interact with onboarding dialog', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('dashboard')
            ->waitForText('Dashboard');

        if ($browser->element('@onboarding-dialog')) {
            $browser
                ->screenshotElement('@onboarding-dialog', 'dashboard-onboarding-dialog')
                ->click('@skip-onboarding')
                ->pause(500)
                ->screenshotElement('@main', 'dashboard-after-skip-onboarding');
        }
    });
});

it('can see metrics', function () {
    $user = User::where('email', 'support@beacon-hq.dev')->first();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 1350);

        $browser->deleteCookie('beacon_session');

        $browser
            ->loginAs($user)
            ->visitRoute('dashboard')
            ->pause(5000);

        $browser
            ->screenshotElement('@main', 'dashboard-metrics')
            ->addCookie('hide-menu-bar', 1)
            ->refresh()
            ->pause(5000)
            ->screenshotElement('@metric-card-total_flags', 'dashboard-metrics-total-flags')
            ->screenshotElement('@metric-card-changes', 'dashboard-metrics-changes')
            ->screenshotElement('@metric-card-created', 'dashboard-metrics-created')
            ->screenshotElement('@metric-card-complete', 'dashboard-metrics-completed')
            ->screenshotElement('@metric-card-system-health', 'dashboard-metrics-system-health')
            ->screenshotElement('@metric-card-flag-types', 'dashboard-metrics-flag-types')
            ->screenshotElement('@metric-card-usage', 'dashboard-metrics-usage')
            ->screenshotElement('@metric-card-average-age', 'dashboard-metrics-average-age')
            ->screenshotElement('@metric-card-top-usage', 'dashboard-metrics-top-usage')
            ->screenshotElement('@metric-card-oldest', 'dashboard-metrics-oldest-flags')
            ->deleteCookie('hide-menu-bar');
    });
});
