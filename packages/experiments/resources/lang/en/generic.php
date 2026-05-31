<?php

declare(strict_types=1);

return [
    'statuses' => [
        'draft' => 'Draft',
        'scheduled' => 'Scheduled',
        'active' => 'Active',
        'paused' => 'Paused',
        'ended' => 'Ended',
    ],
    'subject_types' => [
        'page' => 'Page',
        'campaign' => 'Campaign',
        'generic' => 'Generic',
    ],
    'allocation_strategies' => [
        'weighted' => 'Weighted',
        'sticky_weighted' => 'Sticky weighted',
    ],
    'goal_types' => [
        'page_view' => 'Page view',
        'click' => 'Click',
        'form_submission' => 'Form submission',
        'custom_event' => 'Custom event',
    ],
    'audience_rule_types' => [
        'attribute' => 'Attribute',
        'path' => 'Path',
        'query' => 'Query',
        'referrer' => 'Referrer',
        'utm' => 'UTM',
        'segment' => 'Segment',
    ],
    'audience_operators' => [
        'equals' => 'Equals',
        'not_equals' => 'Does not equal',
        'contains' => 'Contains',
        'starts_with' => 'Starts with',
        'ends_with' => 'Ends with',
        'in' => 'Is one of',
        'not_in' => 'Is not one of',
        'exists' => 'Exists',
        'missing' => 'Missing',
    ],
];
