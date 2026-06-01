<?php

declare(strict_types=1);

return [
    'claim_failed' => [
        'message' => 'The link may have expired or already been used. Request access again with the same email address.',
        'title' => 'This access link is not available',
    ],
    'message' => [
        'back' => 'Request a new link',
    ],
    'portal' => [
        'gated_resource' => 'Gated resource',
        'grant_description' => 'Access is currently available.',
        'grant_expires_description' => 'Access is available until :date.',
        'grant_status' => [
            'active' => 'Access active',
            'expired' => 'Access expired',
            'revoked' => 'Access revoked',
        ],
        'registration_description' => 'Access request is being reviewed.',
        'registration_status' => [
            'approved' => 'Access approved',
            'expired' => 'Access expired',
            'claimed' => 'Access claimed',
            'pending' => 'Request pending',
            'rejected' => 'Request rejected',
        ],
    ],
    'request' => [
        'email' => 'Email address',
        'heading' => 'Request access to :area',
        'intro' => 'Enter your email address. If a preview place is available, we will send your invitation and next steps.',
        'or_email' => 'or enter your email address',
        'submit' => 'Request Access',
        'title' => 'Request access to :area',
    ],
    'request_submitted' => "Thanks. You're on the early access list. If your request is approved, we'll email the approval and setup steps. You'll need to accept the GitHub invite within seven days.",
    'request_unavailable' => 'Access requests are not available for this area right now.',
    'cta' => [
        'submit' => 'Request access',
    ],
];
