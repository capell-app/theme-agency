<?php

declare(strict_types=1);

return [
    'actions' => [
        'create_note' => 'Create note',
        'public_action' => 'Run Public Action',
        'queue_agent_capability' => 'Queue agent capability',
        'send_email' => 'Send email',
        'subscribe_user' => 'Subscribe user',
        'tag_contact' => 'Tag contact',
        'webhook' => 'Webhook',
    ],
    'dispatcher' => [
        'handler_failed' => 'Automation Studio action :action failed. Check the application logs for details.',
        'handler_missing' => 'No Automation Studio handler is registered for :action.',
        'public_action_key_required' => 'A Public Action key is required.',
        'public_actions_unavailable' => 'Public Actions is not available.',
        'webhook_action_key_required' => 'A Public Action webhook key is required.',
        'contacts_unavailable' => 'Contacts is not available.',
        'contact_site_required' => 'A site id is required to resolve a contact.',
        'contact_tag_required' => 'At least one contact tag is required.',
        'contact_note_required' => 'A contact note summary is required.',
        'newsletter_unavailable' => 'Newsletter is not available.',
        'newsletter_site_required' => 'A site id is required to subscribe a user.',
        'newsletter_email_required' => 'An email address is required to subscribe a user.',
        'email_studio_unavailable' => 'Email Studio is not available.',
        'email_template_key_required' => 'An Email Studio template key is required.',
        'email_recipient_required' => 'At least one email recipient is required.',
        'agent_bridge_unavailable' => 'Agent Bridge is not available.',
        'agent_capability_key_required' => 'An Agent Bridge capability key is required.',
    ],
    'fields' => [
        'action' => 'Action',
        'action_type' => 'Action type',
        'actions' => 'Actions',
        'conditions' => 'Conditions',
        'finished_at' => 'Finished',
        'idempotency_key' => 'Idempotency key',
        'key' => 'Key',
        'message' => 'Message',
        'name' => 'Name',
        'rule' => 'Rule',
        'runs' => 'Runs',
        'settings' => 'Settings',
        'started_at' => 'Started',
        'status' => 'Status',
        'trigger' => 'Trigger',
        'updated_at' => 'Updated',
    ],
    'navigation' => [
        'group' => 'Automation Studio',
    ],
    'resources' => [
        'rules' => 'Automation rules',
        'runs' => 'Automation runs',
    ],
    'rules' => [
        'status' => [
            'active' => 'Active',
            'paused' => 'Paused',
        ],
    ],
    'runs' => [
        'status' => [
            'failed' => 'Failed',
            'pending' => 'Pending',
            'skipped' => 'Skipped',
            'succeeded' => 'Succeeded',
        ],
    ],
    'triggers' => [
        'access_approved' => 'Access approved',
        'campaign_converted' => 'Campaign converted',
        'form_submitted' => 'Form submitted',
        'page_published' => 'Page published',
    ],
];
