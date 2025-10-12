<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

it('can login', function () {
    [, $user, ] = createOwner();

    $user->password = 'Password123!';
    $user->two_factor_secret = 'testing';
    $user->two_factor_confirmed_at = now();
    $user->save();

    Config::set('beacon.billing.enabled', false);

    $page = visit('/')
        ->resize(500, 850);

    $page->script('document.cookie = \'hide-menu-bar=1; path=/\'');
    $page->script('document.cookie = \'fortify-testing=0; path=/\'');

    $page->navigate(route('login'));

    $page->assertVisible('[data-test="button-login-submit"]')
        ->screenshot(filename: 'login-form')
        ->fill('email', $user->email)
        ->fill('password', 'Password123!')
        ->click('[data-test="button-login-submit"]');

    $page->assertSee('Verification')
        ->wait(1)
        ->screenshot(filename: 'login-verification');
});
