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

    'commands' => [
        'apply_retention' => [
            'summary' => 'Applied :rules privacy retention rule(s), matched :matched record(s), and affected :affected record(s).',
        ],
    ],

    'admin' => [
        'navigation_group' => 'Privacy center',
        'resources' => [
            'consent_policies' => 'Consent policies',
            'consent_records' => 'Consent records',
            'policy_acceptances' => 'Policy acceptances',
            'privacy_requests' => 'Privacy requests',
            'retention_rules' => 'Retention rules',
        ],
        'actions' => [
            'mark_fulfilled' => 'Mark fulfilled',
            'mark_verified' => 'Mark verified',
            'reject' => 'Reject',
        ],
        'fields' => [
            'accepted_at' => 'Accepted',
            'action' => 'Action',
            'category' => 'Category',
            'content_hash' => 'Content hash',
            'context' => 'Context',
            'data_domain' => 'Data domain',
            'decided_at' => 'Decided',
            'decision' => 'Decision',
            'due_at' => 'Due',
            'effective_at' => 'Effective',
            'email_hash' => 'Email hash',
            'expires_at' => 'Expires',
            'fulfilled_at' => 'Fulfilled',
            'is_active' => 'Active',
            'jurisdiction' => 'Jurisdiction',
            'key' => 'Key',
            'legal_basis' => 'Legal basis',
            'metadata' => 'Metadata',
            'overdue' => 'Overdue',
            'policy_key' => 'Policy key',
            'policy_type' => 'Policy type',
            'policy_version' => 'Policy version',
            'published_at' => 'Published',
            'record_type' => 'Record type',
            'reference' => 'Reference',
            'rejected_at' => 'Rejected',
            'rejection_reason' => 'Rejection reason',
            'request_type' => 'Request type',
            'retention_days' => 'Retention days',
            'retired_at' => 'Retired',
            'revoked_at' => 'Revoked',
            'site_id' => 'Site ID',
            'status' => 'Status',
            'subject_id' => 'Subject ID',
            'subject_type' => 'Subject type',
            'submitted_at' => 'Submitted',
            'title' => 'Title',
            'type' => 'Type',
            'verified_at' => 'Verified',
            'version' => 'Version',
            'workflow_payload' => 'Workflow payload',
        ],
        'messages' => [
            'privacy_request_fulfilled' => 'Privacy request marked fulfilled.',
            'privacy_request_rejected' => 'Privacy request rejected.',
            'privacy_request_verified' => 'Privacy request marked verified.',
        ],
        'widgets' => [
            'active_retention_rules' => 'Active retention rules',
            'consent_records' => 'Consent records',
            'granted_consents' => 'Granted consents',
            'open_privacy_requests' => 'Open privacy requests',
        ],
    ],
];
