<?php

declare(strict_types=1);

namespace Capell\Comments\Support;

use Capell\Comments\Enums\CommentIdentityMode;
use Capell\Comments\Enums\CommentPublicationPolicy;
use Capell\Comments\Enums\CommentVerificationFlow;
use Capell\Comments\Settings\CommentSettings;
use Throwable;

class CommentSettingsResolver
{
    public function enabled(?int $siteId = null, ?string $commentableType = null): bool
    {
        return (bool) $this->value('enabled', (bool) config('capell-comments.enabled', true), $siteId, $commentableType);
    }

    public function identityMode(?int $siteId = null, ?string $commentableType = null): CommentIdentityMode
    {
        return CommentIdentityMode::tryFrom((string) $this->value('identity_mode', (string) config('capell-comments.identity_mode', CommentIdentityMode::Both->value), $siteId, $commentableType))
            ?? CommentIdentityMode::Both;
    }

    public function publicationPolicy(?int $siteId = null, ?string $commentableType = null): CommentPublicationPolicy
    {
        return CommentPublicationPolicy::tryFrom((string) $this->value('publication_policy', (string) config('capell-comments.publication_policy', CommentPublicationPolicy::RequireApproval->value), $siteId, $commentableType))
            ?? CommentPublicationPolicy::RequireApproval;
    }

    public function verificationFlow(?int $siteId = null, ?string $commentableType = null): CommentVerificationFlow
    {
        return CommentVerificationFlow::tryFrom((string) $this->value('verification_flow', (string) config('capell-comments.verification_flow', CommentVerificationFlow::VerifyThenModerate->value), $siteId, $commentableType))
            ?? CommentVerificationFlow::VerifyThenModerate;
    }

    public function requiresEmailVerification(?int $siteId = null, ?string $commentableType = null): bool
    {
        return (bool) $this->value('require_email_verification', (bool) config('capell-comments.require_email_verification', true), $siteId, $commentableType);
    }

    public function maxDepth(?int $siteId = null, ?string $commentableType = null): int
    {
        return max(0, (int) $this->value('max_depth', (int) config('capell-comments.max_depth', 4), $siteId, $commentableType));
    }

    private function value(string $key, mixed $fallback, ?int $siteId, ?string $commentableType): mixed
    {
        try {
            /** @var CommentSettings $settings */
            $settings = resolve(CommentSettings::class);
        } catch (Throwable) {
            return $fallback;
        }

        $value = property_exists($settings, $key) ? $settings->{$key} : $fallback;
        $value = $this->overrideValue($settings->commentable_type_overrides, $commentableType, $key, $value);

        return $this->overrideValue($settings->site_overrides, $siteId === null ? null : (string) $siteId, $key, $value);
    }

    /**
     * @param  array<string, string|int|bool|null|array<string, string|int|bool|null>>|list<array<string, string|int|bool|null>>  $overrides
     */
    private function overrideValue(array $overrides, ?string $overrideKey, string $settingKey, mixed $fallback): mixed
    {
        if ($overrideKey === null) {
            return $fallback;
        }

        $override = is_array($overrides[$overrideKey] ?? null)
            ? $overrides[$overrideKey]
            : $this->listOverride($overrides, $overrideKey);

        if (! is_array($override)) {
            return $fallback;
        }

        return array_key_exists($settingKey, $override) ? $override[$settingKey] : $fallback;
    }

    /**
     * @param  array<string, string|int|bool|null|array<string, string|int|bool|null>>|list<array<string, string|int|bool|null>>  $overrides
     * @return array<string, mixed>|null
     */
    private function listOverride(array $overrides, string $overrideKey): ?array
    {
        foreach ($overrides as $override) {
            if (! is_array($override)) {
                continue;
            }

            $candidateKey = $override['key']
                ?? $override['site_id']
                ?? $override['commentable_type']
                ?? null;

            if ((string) $candidateKey === $overrideKey) {
                /** @var array<string, mixed> $override */
                return $override;
            }
        }

        return null;
    }
}
