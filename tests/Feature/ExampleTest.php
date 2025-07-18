<?php

declare(strict_types=1);

it('returns a successful response', function () {
    $response = $this->get(route('welcome'));

    $response->assertStatus(200);
});
