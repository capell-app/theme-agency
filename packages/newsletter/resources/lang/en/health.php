<?php

declare(strict_types=1);

return [
    'form_subscription' => [
        'label' => 'Newsletter form subscription capture',
        'passed' => 'Form submissions are wired to create subscribers and record consent evidence.',
        'listener_missing' => 'The Form Builder submission listener is not registered, so form submissions cannot create subscribers.',
        'tables_missing' => 'Missing subscriber or consent table(s): :tables.',
        'remediation' => 'Ensure NewsletterServiceProvider registers the FormSubmitted listener and the newsletter migrations have run.',
    ],
    'provider_sync_retry' => [
        'label' => 'Newsletter provider sync retry pipeline',
        'passed' => 'Provider sync failures are persisted and the retry action can query due attempts without dispatching jobs.',
        'tables_missing' => 'Missing sync attempt table(s): :tables.',
        'pipeline_unavailable' => 'The provider sync retry action or command is unavailable.',
        'remediation' => 'Run the newsletter migrations and ensure the retry command and action are registered.',
    ],
    'provider_webhooks' => [
        'label' => 'Newsletter provider webhook idempotency',
        'passed' => 'Provider webhooks are routed, normalized, and de-duplicated through the processed webhook events table.',
        'tables_missing' => 'Missing webhook idempotency table(s): :tables.',
        'route_missing' => 'The provider webhook route is not registered.',
        'action_missing' => 'The provider webhook action cannot be resolved.',
        'remediation' => 'Run the newsletter migrations and ensure the package web routes and provider webhook action are loaded.',
    ],
    'segments' => [
        'label' => 'Newsletter segment evaluation',
        'passed' => 'Static and dynamic segments evaluate to a typed subscriber query builder.',
        'tables_missing' => 'Segment evaluation cannot run because required table(s) are missing: :tables.',
        'builder_missing' => 'Segment evaluation did not return a subscriber query builder.',
        'remediation' => 'Run the newsletter migrations and inspect EvaluateNewsletterSegmentAction if the query builder check still fails.',
    ],
];
