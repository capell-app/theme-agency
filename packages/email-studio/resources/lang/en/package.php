<?php

declare(strict_types=1);

return [
    'commands' => [
        'positive_integer' => 'The :option option must be a positive integer.',
        'prune_bodies' => [
            'dry_run' => 'Dry run only. No rendered email bodies were changed.',
            'summary' => 'Email Studio body retention matched :matched message(s) older than :days day(s), and cleared :pruned rendered body snapshot(s).',
        ],
        'purge_tracked_emails' => [
            'dry_run' => 'Dry run only. No tracked email rows were deleted.',
            'summary' => 'MailTracker retention matched :matched email(s) older than :days day(s), deleted :clicks click row(s), and deleted :emails email row(s).',
        ],
    ],
    'description' => 'Template-driven transactional email with static package defaults, editable overrides, auth email replacement, theme editing, delivery auditing, provider adapters, and suppressions.',
    'health' => [
        'components' => [
            'address_normalizer' => 'EmailAddressNormalizer',
            'activate_variant_action' => 'ActivateEmailTemplateVariantAction',
            'capture_theme_screenshot_action' => 'CaptureEmailTemplateThemeScreenshotAction',
            'check_suppression_action' => 'CheckEmailSuppressionAction',
            'create_override_action' => 'CreateEmailTemplateOverrideAction',
            'deliver_action' => 'DeliverEmailMessageAction',
            'email_events_table' => 'email_events table',
            'email_messages_table' => 'email_messages table',
            'email_recipients_table' => 'email_recipients table',
            'email_replies_table' => 'email_replies table',
            'email_suppressions_table' => 'email_suppressions table',
            'email_template_themes_table' => 'email_template_themes table',
            'email_templates_table' => 'email_templates table',
            'provider_adapter' => 'at least one registered provider adapter',
            'provider_normalizer' => ':provider provider webhook and reply normalizers',
            'render_definition_action' => 'RenderEmailTemplateDefinitionAction',
            'render_action' => 'RenderEmailTemplateAction',
            'send_test_action' => 'SendEmailTemplateTestAction',
            'send_job' => 'SendEmailJob',
            'suppress_email_action' => 'SuppressEmailAddressAction',
            'template_registry' => 'EmailTemplateRegistry',
            'variable_renderer' => 'EmailVariableRenderer',
        ],
        'missing' => 'Missing: :components.',
        'provider_delivery' => [
            'label' => 'Queued email delivery records provider results per recipient',
            'passed' => 'Delivery action, send job, message and recipient tables, and :count provider adapter(s) are available.',
            'remediation' => 'Run Email Studio migrations and confirm provider adapters are registered in EmailProviderRegistry.',
        ],
        'provider_events' => [
            'label' => 'Provider webhook and inbound reply normalization foundations are present',
            'passed' => 'Registered adapters normalize webhook events and inbound replies, and the events and replies tables are available.',
            'remediation' => 'Run Email Studio migrations and confirm registered provider adapters can normalize webhook events and inbound replies.',
        ],
        'suppressions' => [
            'label' => 'Suppressions are enforced before provider handoff',
            'passed' => 'Suppression check and capture actions, address normalizer, and suppressions table are available.',
            'remediation' => 'Run Email Studio migrations and confirm the suppression actions and normalizer are present.',
        ],
        'template_rendering' => [
            'label' => 'Email templates render through database variants and static registered definitions',
            'passed' => 'Template render actions, variable renderer, template registry, and templates table are available.',
            'remediation' => 'Run Email Studio migrations and confirm render actions, registry, and renderer are present.',
        ],
        'template_authoring' => [
            'label' => 'Template authoring resources, themes, and test-send actions are available',
            'passed' => 'Template override, activation, test-send, screenshot capture actions, and theme table are available.',
            'remediation' => 'Run Email Studio authoring migrations and confirm authoring actions are present.',
        ],
    ],
];
