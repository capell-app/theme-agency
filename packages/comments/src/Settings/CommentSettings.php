<?php

declare(strict_types=1);

namespace Capell\Comments\Settings;

use Capell\Comments\Enums\CommentIdentityMode;
use Capell\Comments\Enums\CommentPublicationPolicy;
use Capell\Comments\Enums\CommentVerificationFlow;
use Capell\Comments\Filament\Settings\CommentSettingsSchema;
use Capell\Core\Contracts\SettingsContract;
use Capell\Core\Contracts\SettingsSchemaContract;
use Spatie\LaravelSettings\Settings;

class CommentSettings extends Settings implements SettingsContract, SettingsSchemaContract
{
    public bool $enabled = true;

    public string $identity_mode = CommentIdentityMode::Both->value;

    public string $publication_policy = CommentPublicationPolicy::RequireApproval->value;

    public string $verification_flow = CommentVerificationFlow::VerifyThenModerate->value;

    public bool $require_email_verification = true;

    public bool $auto_inject = false;

    public int $max_depth = 4;

    public int $root_page_size = 20;

    public int $reply_page_size = 5;

    public int $token_expiry_hours = 72;

    /** @phpstan-var array<string, string|int|bool|null|array<string, string|int|bool|null>>|list<array<string, string|int|bool|null>> */
    public array $site_overrides = [];

    /** @phpstan-var array<string, string|int|bool|null|array<string, string|int|bool|null>>|list<array<string, string|int|bool|null>> */
    public array $commentable_type_overrides = [];

    public static function group(): string
    {
        return 'comments';
    }

    public static function schema(): string
    {
        return CommentSettingsSchema::class;
    }
}
