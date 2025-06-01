<?php

declare(strict_types=1);

return [
    'enabled' => env('BEACON_ENABLED', false),
    'billing' => [
        'enabled' => env('BEACON_BILLING_ENABLED', false),
        'trial_fraud_limit' => 20,
    ],
    'teams' => [
        'invitation_expiration' => new \DateInterval(env('USER_INVITATION_EXPIRATION', 'P1D')),
    ]
];
