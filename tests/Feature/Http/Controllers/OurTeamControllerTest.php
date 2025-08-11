<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

it('shows the our team page', function () {
    $this->get(route('company.our-team'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Company/OurTeam'));
});
