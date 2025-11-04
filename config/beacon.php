<?php

declare(strict_types=1);

return [
    'billing' => [
        'trial_fraud_limit' => 20,
    ],
    'api' => [
        'domain' => env('BEACON_API_DOMAIN', null),
    ],
    'teams' => [
        'invitation_expiration' => new \DateInterval(env('USER_INVITATION_EXPIRATION', 'P1D')),
    ],
    'docs' => [
        'url' => env('BEACON_DOCS_URL', '/docs'),
    ],
    'metrics' => [
        'request' => [
            'reporting' => [
                'sample_rate' => env('BEACON_REQUEST_METRICS_SAMPLE_RATE', 1.0),
            ],
        ],
    ],
];
