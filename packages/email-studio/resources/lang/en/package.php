<?php

declare(strict_types=1);

return [
    'commands' => [
        'positive_integer' => 'The :option option must be a positive integer.',
        'prune_bodies' => [
            'dry_run' => 'Dry run only. No rendered email bodies were changed.',
            'summary' => 'Email Studio body retention matched :matched message(s) older than :days day(s), and cleared :pruned rendered body snapshot(s).',
        ],
    ],
    'description' => 'Template-driven transactional email, delivery auditing, provider events, replies, and suppressions.',
    'health' => [
        'components' => [
            'address_normalizer' => 'EmailAddressNormalizer',
            'check_suppression_action' => 'CheckEmailSuppressionAction',
            'deliver_action' => 'DeliverEmailMessageAction',
            'email_events_table' => 'email_events table',
            'email_messages_table' => 'email_messages table',
            'email_recipients_table' => 'email_recipients table',
            'email_replies_table' => 'email_replies table',
            'email_suppressions_table' => 'email_suppressions table',
            'email_templates_table' => 'email_templates table',
            'provider_adapter' => 'at least one registered provider adapter',
            'provider_normalizer' => ':provider provider webhook and reply normalizers',
            'render_action' => 'RenderEmailTemplateAction',
            'send_job' => 'SendEmailJob',
            'suppress_email_action' => 'SuppressEmailAddressAction',
            'variable_renderer' => 'EmailVariableRenderer',
        ],
        'missing' => 'Missing: :components.',
        'provider_delivery' => [
            'label' => 'Queued email delivery records provider results per recipient',
            'passed' => 'Delivery action, send job, message and recipient tables, and :count provider adapter(s) are available.',
            'remediation' => 'Run Email Studio migrations and confirm provider adapters are registered in EmailProviderRegistry.',
        ],
        'provider_events' => [
            'label' => 'Provider webhook events and inbound replies normalize into local records',
            'passed' => 'Registered adapters normalize webhook events and inbound replies, and the events and replies tables are available.',
            'remediation' => 'Run Email Studio migrations and confirm registered provider adapters can normalize webhook events and inbound replies.',
        ],
        'suppressions' => [
            'label' => 'Suppressions are enforced before provider handoff',
            'passed' => 'Suppression check and capture actions, address normalizer, and suppressions table are available.',
            'remediation' => 'Run Email Studio migrations and confirm the suppression actions and normalizer are present.',
        ],
        'template_rendering' => [
            'label' => 'Email templates render through typed template actions',
            'passed' => 'Template render action, variable renderer, and templates table are available.',
            'remediation' => 'Run Email Studio migrations and confirm the render action and renderer are present.',
        ],
    ],
];
