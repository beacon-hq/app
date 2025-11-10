<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Pennant\Feature;

it('can register a new user', function () {
    Feature::define('billing-enabled', fn () => false);
    $page = visit('/');

    $page->resize(600, 1000);

    $page->script('document.cookie = \'hide-menu-bar=1; path=/\'');
    $page->script('document.cookie = \'fortify-testing=0; path=/\'');

    $page->navigate('/register')
        ->assertPathIs('/register');

    $page->wait(2);

    $page->assertVisible('[data-test="input-register-first-name"]')
        ->click('[data-test="card-register"]')
        ->screenshot(filename: 'register-form')
        ->fill('[data-test="input-register-first-name"]', 'Beacon')
        ->fill('[data-test="input-register-last-name"]', 'Demo')
        ->fill('[data-test="input-register-email"]', 'support-' . time() . '@beaconhq.io')
        ->fill('[data-test="input-register-password"]', 'Password123!')
        ->fill('[data-test="input-register-password-confirmation"]', 'Password123!')
        ->click('[data-test="button-register-submit"]');

    $page->assertUrlIs('*/user/verify-email')
        ->wait(1)
        ->resize(600, 800)
        ->screenshot(filename: 'register-email-confirmation');

    $user = User::query()->latest()->first();
    $user->email_verified_at = now();
    $user->save();

    $page->resize(600, 1300)
        ->navigate(route('two-factor.setup'))
        ->wait(1)
        ->screenshot(filename: 'register-two-factor-setup');

    $page->resize(600, 1250)
        ->navigate(route('two-factor.confirmed'))
        ->wait(1)
        ->screenshot(filename: 'register-two-factor-confirmed');
});
