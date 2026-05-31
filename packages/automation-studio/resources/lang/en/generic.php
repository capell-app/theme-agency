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
        'handler_missing' => 'No Automation Studio handler is registered for :action.',
        'public_action_key_required' => 'A Public Action key is required.',
        'public_actions_unavailable' => 'Public Actions is not available.',
    ],
    'rules' => [
        'status' => [
            'active' => 'Active',
            'paused' => 'Paused',
        ],
    ],
    'triggers' => [
        'access_approved' => 'Access approved',
        'campaign_converted' => 'Campaign converted',
        'form_submitted' => 'Form submitted',
        'page_published' => 'Page published',
    ],
];
