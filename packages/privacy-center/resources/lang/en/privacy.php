<?php

declare(strict_types=1);

return [
    'cookie_categories' => [
        'essential' => 'Essential',
        'analytics' => 'Analytics',
        'marketing' => 'Marketing',
        'preferences' => 'Preferences',
        'functional' => 'Functional',
    ],

    'consent_decisions' => [
        'granted' => 'Granted',
        'denied' => 'Denied',
        'withdrawn' => 'Withdrawn',
        'expired' => 'Expired',
    ],

    'policy_types' => [
        'privacy' => 'Privacy policy',
        'cookie' => 'Cookie policy',
        'terms' => 'Terms',
        'data_processing' => 'Data processing',
    ],

    'privacy_request_statuses' => [
        'submitted' => 'Submitted',
        'verifying' => 'Verifying',
        'processing' => 'Processing',
        'fulfilled' => 'Fulfilled',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
    ],

    'privacy_request_types' => [
        'access' => 'Access',
        'export' => 'Export',
        'delete' => 'Delete',
        'rectify' => 'Rectify',
        'restrict' => 'Restrict',
        'object' => 'Object',
    ],

    'retention_actions' => [
        'delete' => 'Delete',
        'anonymize' => 'Anonymize',
        'review' => 'Review',
    ],
];
