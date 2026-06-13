<?php

declare(strict_types=1);

return [
    'enabled' => true,
    'auto_inject' => true,
    'ignored_paths' => [
        '/admin*',
        '/live-chat*',
    ],

    'public_path_prefix' => 'live-chat',
    'default_site_id' => 1,
    'default_timezone' => 'Europe/London',
    'max_message_length' => 4000,
    'max_attachment_count' => 5,
    'low_confidence_threshold' => 0.55,
    'attachments' => [
        'disk' => 'local',
        'max_kilobytes' => 10240,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'txt', 'csv', 'doc', 'docx'],
    ],

    'tables' => [
        'conversations' => 'live_chat_conversations',
        'messages' => 'live_chat_messages',
        'availability_windows' => 'live_chat_availability_windows',
        'availability_exceptions' => 'live_chat_availability_exceptions',
        'escalation_rules' => 'live_chat_escalation_rules',
        'knowledge_sources' => 'live_chat_knowledge_sources',
    ],

    'widget' => [
        'agent_name' => 'Lara',
        'avatar_initials' => 'LA',
        'brand_name' => 'Support',
        'welcome_message' => 'Hi, I am Lara, the AI assistant. Ask a question or leave your details and we will help.',
        'ai_disclosure' => 'I am an AI assistant. I can answer from approved content and bring in a person when needed.',
        'message_placeholder' => 'Type your message',
        'details_first_label' => 'Leave details first',
        'message_first_label' => 'Ask first',
        'handoff_label' => 'Ask a person',
        'offline_message' => 'We are currently out of the office. Please leave your contact details and we will get back to you as soon as we can.',
        'labels' => [
            'close' => 'Close',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'company' => 'Company',
            'send' => 'Send',
            'sending' => 'Sending...',
            'request_failed' => 'Message could not be sent. Please try again.',
            'handoff_requires_conversation' => 'Send a message first so we can include the chat history.',
            'handoff_requested' => 'Human handoff requested.',
            'handoff_failed' => 'Handoff could not be requested. Please try again.',
        ],
        'primary_color' => '#087765',
        'surface_color' => '#fcfffb',
        'text_color' => '#101715',
        'proactive_triggers' => [
            [
                'name' => 'pricing',
                'path' => '*pricing*',
                'delay_seconds' => 12,
                'message' => 'Need help choosing the right option?',
            ],
            [
                'name' => 'contact',
                'path' => '*contact*',
                'delay_seconds' => 8,
                'message' => 'Want to leave a message for the team?',
            ],
        ],
    ],

    'business_hours' => [
        'timezone' => 'Europe/London',
        'weekly' => [
            ['day' => 1, 'opens_at' => '09:00', 'closes_at' => '17:00'],
            ['day' => 2, 'opens_at' => '09:00', 'closes_at' => '17:00'],
            ['day' => 3, 'opens_at' => '09:00', 'closes_at' => '17:00'],
            ['day' => 4, 'opens_at' => '09:00', 'closes_at' => '17:00'],
            ['day' => 5, 'opens_at' => '09:00', 'closes_at' => '17:00'],
        ],
    ],

    'escalation' => [
        'manual_handoff_enabled' => true,
        'default_queue' => 'support',
        'urgent_keywords' => [
            'urgent',
            'emergency',
            'refund',
            'complaint',
            'legal',
            'solicitor',
            'lawsuit',
            'chargeback',
            'data breach',
        ],
    ],
];
