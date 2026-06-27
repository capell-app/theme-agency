<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Resolves the commentable model from a decoded thread-key payload: maps the
 * morph alias to a model class, loads it, and confirms the site/language scope
 * matches. Returns null for any malformed, unknown, or scope-mismatched payload.
 *
 * @method static ?Model run(array<array-key, mixed> $payload)
 */
class ResolveCommentableFromPayloadAction
{
    use AsObject;

    /**
     * @param  array<array-key, mixed>  $payload
     */
    public function handle(array $payload): ?Model
    {
        $type = $payload['type'] ?? null;
        $id = $payload['id'] ?? null;
        $siteId = $payload['site_id'] ?? null;
        $languageId = $payload['language_id'] ?? null;

        if (! is_string($type) || ! is_numeric($id)) {
            return null;
        }

        $class = Relation::getMorphedModel($type) ?? $type;
        if (! is_string($class) || ! class_exists($class) || ! is_a($class, Model::class, true)) {
            return null;
        }

        /** @var Model|null $model */
        $model = $class::query()->find((int) $id);

        if (! $model instanceof Model) {
            return null;
        }

        if (is_numeric($siteId) && $this->intAttribute($model, 'site_id') !== (int) $siteId) {
            return null;
        }

        if (is_numeric($languageId) && $this->intAttribute($model, 'language_id') !== (int) $languageId) {
            return null;
        }

        return $model;
    }

    /**
     * Integer value of a model attribute, mirroring an `(int)` cast: absent or
     * non-numeric attributes resolve to 0.
     */
    private function intAttribute(Model $model, string $key): int
    {
        $value = array_key_exists($key, $model->getAttributes())
            ? $model->getAttribute($key)
            : null;

        return is_numeric($value) ? (int) $value : 0;
    }
}
