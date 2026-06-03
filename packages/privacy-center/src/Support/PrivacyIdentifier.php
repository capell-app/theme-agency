<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use RuntimeException;

final class PrivacyIdentifier
{
    public static function morphType(?Model $model): ?string
    {
        if (! $model instanceof Model) {
            return null;
        }

        $alias = array_search($model::class, Relation::morphMap(), true);

        return is_string($alias) ? $alias : $model::class;
    }

    public static function hashNullable(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return hash('sha256', self::hashSecret() . ':' . Str::lower(trim($value)));
    }

    /**
     * Resolves the salt used for consent-evidence hashing.
     *
     * Fails loudly rather than falling back to a guessable literal: a
     * predictable salt would make hashed compliance evidence (IP,
     * user-agent) trivially reversible across every install.
     *
     * @throws RuntimeException when no hash secret is configured.
     */
    private static function hashSecret(): string
    {
        $configuredSecret = config('capell-privacy-center.hash_secret');

        if (is_string($configuredSecret) && $configuredSecret !== '') {
            return $configuredSecret;
        }

        $appKey = config('app.key');

        if (is_string($appKey) && $appKey !== '') {
            return $appKey;
        }

        throw new RuntimeException(
            'Privacy Center cannot hash consent evidence: set capell-privacy-center.hash_secret '
            . '(CAPELL_PRIVACY_CENTER_HASH_SECRET) or app.key. Refusing to use a guessable salt.',
        );
    }
}
