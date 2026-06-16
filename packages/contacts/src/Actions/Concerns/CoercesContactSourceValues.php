<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions\Concerns;

use Illuminate\Database\Eloquent\Model;

trait CoercesContactSourceValues
{
    private function relatedModel(Model $model, string $relation, bool $allowLazyLoad = true): ?Model
    {
        $loaded = $model->relationLoaded($relation) ? $model->getRelation($relation) : null;

        if ($loaded instanceof Model) {
            return $loaded;
        }

        if (! $allowLazyLoad || ! $model->exists || ! method_exists($model, $relation)) {
            return null;
        }

        $related = $model->{$relation}()->first();

        return $related instanceof Model ? $related : null;
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function intValue(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
