<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

it('can create new policy', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('policies.index')
            ->waitForText('Policies')
            ->screenshotElement('@main', 'policies-initial');

        $browser
            ->click('@button-new-policy')
            ->waitForText('New Policy')
            ->screenshotElement('@main', 'policies-form-create');

        $browser
            ->type('@input-policy-name', 'Demo Policy')
            ->type('@input-policy-description', 'This is a demo policy.')
            ->screenshot('policies-form-create-fill')
            ->click('@button-policy-submit')
            ->waitForText('Demo Policy', 10);

        $browser
            ->visitRoute('policies.index')
            ->waitForText('Policies');

        $browser
            ->click('@button-new-policy')
            ->waitForText('New Policy');

        $browser
            ->type('@input-policy-name', 'Demo Policy Two')
            ->type('@input-policy-description', 'This is a demo policy.')
            ->click('@button-policy-submit')
            ->waitForText('Demo Policy', 10);
    });
});

it('can view policies list', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 700);

        $browser
            ->loginAs($user)
            ->visitRoute('policies.index')
            ->waitForText('Policies')
            ->screenshotElement('@main', 'policies-list');
    });
});

it('can edit policies', function () {
    $user = createBrowserUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 900);

        $browser
            ->loginAs($user)
            ->visitRoute('policies.index')
            ->waitForText('Policies');

        $browser
            ->click('@button-policies-edit')
            ->waitForText('Edit Policy')
            ->pause(500)
            ->screenshotElement('@main', 'policies-edit');

        $browser
            ->type('@input-policy-description', 'This is an updated demo policy.')
            ->screenshot('policies-form-edit-fill')
            ->click('@button-policy-submit');
    });
});



it('can edit policy conditions', function () {
    $user = createBrowserUser();

    $this->browse(callback: function (Browser $browser) use ($user) {
        $browser->resize(3024 / 2, 900);

        $browser
            ->loginAs($user)
            ->visitRoute('policies.index')
            ->waitForText('Policies');

        $browser
            ->click('@button-policies-edit')
            ->waitForText('Edit Policy')
            ->pause(500);

        $browser
            ->click('@button-policies-add-conditions')
            ->screenshotElement('@policies-form-definition', 'policies-form-initial-condition');


        $browser
            ->click('@select-policies-definition-type-0')
            ->click('@select-option-policies-definition-type-0-expression')
            ->type('@input-policies-definition-subject-0', 'user.id')
            ->click('@select-policies-definition-operator-0')
            ->click('@select-option-policies-definition-operator-0-equals')
            ->pause(300)
            ->type('@input-policy-value-0', '123')
            ->click('@main')
            ->screenshotElement('@policies-form-definition', 'policies-form-expression');

        $browser
            ->click('@select-policies-definition-type-0')
            ->pause(300)
            ->click('@select-option-policies-definition-type-0-policy')
            ->pause(300)
            ->click('@select-policies-definition-policy-0')
            ->pause(300)
            ->click('@select-option-policies-definition-policy-0-0')
            ->click('@main')
            ->screenshotElement('@policies-form-definition', 'policies-form-policy');

        $browser
            ->click('@select-policies-definition-type-0')
            ->pause(300)
            ->click('@select-option-policies-definition-type-0-datetime')
            ->pause(300)
            ->click('@select-policies-definition-datetime-0')
            ->pause(300)
            ->click('@select-option-policies-definition-datetime-0-after')
            ->click('@datepicker-policies-definition-datetime-0')
            ->pause(1000)
            ->click('.rdp-tbody tr:nth-child(5) td:nth-child(5) button')
            ->type('@timepicker-policies-definition-datetime-0-hour', '03')
            ->type('@timepicker-policies-definition-datetime-0-minute', '30')
            ->type('@timepicker-policies-definition-datetime-0-second', '47')
            ->click('@select-policies-definition-timezone-0')
            ->pause(300)
            ->click('@select-policies-definition-timezone-0-option-5')
            ->click('@main')
            ->screenshotElement('@policies-form-definition', 'policies-form-datetime');

        $browser
            ->click('@select-policies-definition-type-0')
            ->click('@select-option-policies-definition-type-0-expression')
            ->type('@input-policies-definition-subject-0', 'user.id')
            ->click('@select-policies-definition-operator-0')
            ->click('@select-option-policies-definition-operator-0-equals')
            ->pause(300)
            ->type('@input-policy-value-0', '123')
            ->click('@main')
            ->screenshotElement('@policies-form-definition', 'policies-form-expression');

        $browser
            ->click('@button-policies-add')
            ->pause(500);

        $browser
            ->click('@select-policies-definition-type-1')
            ->click('@select-option-policies-definition-type-1-operator')
            ->click('@select-policies-definition-operator-1')
            ->click('@select-option-policies-definition-operator-1-AND')
            ->click('@main');

        $browser
            ->click('@button-policies-add')
            ->pause(500);

        $browser->screenshotElement('@policies-form-definition', 'policies-form-operator');

        $browser
            ->click('@select-policies-definition-type-2')
            ->pause(300)
            ->click('@select-option-policies-definition-type-2-datetime')
            ->pause(300)
            ->click('@select-policies-definition-datetime-2')
            ->pause(300)
            ->click('@select-option-policies-definition-datetime-2-after')
            ->click('@datepicker-policies-definition-datetime-2')
            ->pause(1000)
            ->click('.rdp-tbody tr:nth-child(5) td:nth-child(5) button')
            ->type('@timepicker-policies-definition-datetime-2-hour', '03')
            ->type('@timepicker-policies-definition-datetime-2-minute', '30')
            ->type('@timepicker-policies-definition-datetime-2-second', '47')
            ->click('@select-policies-definition-timezone-2')
            ->pause(300)
            ->click('@select-policies-definition-timezone-2-option-5')
            ->click('@main')
            ->screenshotElement('@policies-form-definition', 'policies-form-multiple');

        $browser
            ->click('@button-policies-add')
            ->pause(500);

        $browser
            ->click('@select-policies-definition-type-3')
            ->click('@select-option-policies-definition-type-3-operator')
            ->click('@select-policies-definition-operator-3')
            ->click('@select-option-policies-definition-operator-3-AND');

        $browser
            ->click('@button-policies-add')
            ->pause(500);

        $browser
            ->click('@select-policies-definition-type-4')
            ->pause(300)
            ->click('@select-option-policies-definition-type-4-policy')
            ->pause(300)
            ->click('@select-policies-definition-policy-4')
            ->pause(300)
            ->click('@select-option-policies-definition-policy-4-0');

        $browser
            ->click('@main')
            ->screenshotElement('@policies-form-definition', 'policies-form-complex');

    });
});
