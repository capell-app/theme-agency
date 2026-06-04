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
];
