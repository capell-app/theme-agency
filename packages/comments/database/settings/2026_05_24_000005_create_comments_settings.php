<?php

declare(strict_types=1);

use Capell\Comments\Enums\CommentIdentityMode;
use Capell\Comments\Enums\CommentPublicationPolicy;
use Capell\Comments\Enums\CommentVerificationFlow;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $defaults = [
            'comments.enabled' => true,
            'comments.identity_mode' => CommentIdentityMode::Both->value,
            'comments.publication_policy' => CommentPublicationPolicy::RequireApproval->value,
            'comments.verification_flow' => CommentVerificationFlow::VerifyThenModerate->value,
            'comments.require_email_verification' => true,
            'comments.auto_inject' => false,
            'comments.max_depth' => 4,
            'comments.root_page_size' => 20,
            'comments.reply_page_size' => 5,
            'comments.token_expiry_hours' => 72,
            'comments.site_overrides' => [],
            'comments.commentable_type_overrides' => [],
        ];

        foreach ($defaults as $key => $value) {
            if (! $this->migrator->exists($key)) {
                $this->migrator->add($key, $value);
            }
        }
    }
};
