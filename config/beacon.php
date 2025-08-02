<?php

declare(strict_types=1);

return [
    'enabled' => env('BEACON_ENABLED', false),
    'billing' => [
        'enabled' => env('BEACON_BILLING_ENABLED', false),
        'trial_fraud_limit' => 20,
    ],
    'api' => [
        'domain' => env('BEACON_API_DOMAIN', null),
        'rate_limiting' => [
            'enabled' => env('BEACON_API_RATE_LIMITING_ENABLED', true),
        ],
    ],
    'teams' => [
        'invitation_expiration' => new \DateInterval(env('USER_INVITATION_EXPIRATION', 'P1D')),
    ],
    'docs' => [
        'url' => env('BEACON_DOCS_URL', '/docs'),
    ],
    'metrics' => [
        'dashboard' => [
            'enabled' => true,
        ],
        'request' => [
            'enabled' => env('BEACON_REQUEST_METRICS', true),
            'reporting' => [
                'enabled' => env('BEACON_REQUEST_METRICS_REPORTING', true),
                'sample_rate' => env('BEACON_REQUEST_METRICS_SAMPLE_RATE', 1.0),
            ],
        ],
    ],
];
