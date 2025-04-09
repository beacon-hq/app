<?php

declare(strict_types=1);

return [
    'enabled' => env('BEACON_ENABLED', false),
    'teams' => [
        'invitation_expiration' => new \DateInterval(env('USER_INVITATION_EXPIRATION', 'P1D')),
    ]
];
