<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

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

    private static function hashSecret(): string
    {
        $configuredSecret = config('capell-privacy-center.hash_secret');

        if (is_string($configuredSecret) && $configuredSecret !== '') {
            return $configuredSecret;
        }

        $appKey = config('app.key');

        return is_string($appKey) && $appKey !== '' ? $appKey : 'capell-privacy-center';
    }
}
