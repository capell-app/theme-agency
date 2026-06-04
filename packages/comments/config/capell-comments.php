<?php

declare(strict_types=1);

use Capell\Comments\Enums\CommentIdentityMode;
use Capell\Comments\Enums\CommentPublicationPolicy;
use Capell\Comments\Enums\CommentVerificationFlow;
use Capell\Comments\Support\Spam\LocalCommentSpamProvider;
use Illuminate\Support\Env;

return [
    'enabled' => true,
    'route_prefix' => 'capell/comments',
    'identity_mode' => CommentIdentityMode::Both->value,
    'publication_policy' => CommentPublicationPolicy::RequireApproval->value,
    'verification_flow' => CommentVerificationFlow::VerifyThenModerate->value,
    'require_email_verification' => true,
    'auto_inject' => false,
    'max_depth' => 4,
    'root_page_size' => 20,
    'reply_page_size' => 5,
    'token_expiry_hours' => 72,
    'email_hash_secret' => Env::get('CAPELL_COMMENTS_EMAIL_HASH_SECRET'),
    'visitor_hash_secret' => Env::get('CAPELL_COMMENTS_VISITOR_HASH_SECRET'),
    'throttle' => [
        'max_attempts' => 6,
        'decay_seconds' => 60,
    ],
    'spam' => [
        'providers' => [
            LocalCommentSpamProvider::class,
        ],
        'max_links' => 3,
        'blocked_terms' => [],
        'minimum_form_age_seconds' => 2,
    ],
    'notifications' => [
        'moderators' => [],
        'reply_authors' => true,
    ],
    'retention' => [
        'days' => 180,
    ],
];
