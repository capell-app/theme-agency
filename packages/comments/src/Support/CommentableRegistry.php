<?php

declare(strict_types=1);

namespace Capell\Comments\Support;

use Capell\Comments\Data\CommentableTypeData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CommentableRegistry
{
    /** @var array<string, CommentableTypeData> */
    private array $types = [];

    public function register(CommentableTypeData $type): self
    {
        $this->types[$type->key] = $type;

        return $this;
    }

    public function forModel(Model|string $model): ?CommentableTypeData
    {
        $modelClass = is_string($model) ? $model : $model::class;

        foreach ($this->types as $type) {
            if ($type->modelClass === $modelClass || is_a($modelClass, $type->modelClass, true)) {
                return $type;
            }
        }

        return null;
    }

    public function hasModel(Model|string $model): bool
    {
        return $this->forModel($model) instanceof CommentableTypeData;
    }

    /**
     * @return Collection<string, CommentableTypeData>
     */
    public function all(): Collection
    {
        return collect($this->types);
    }
}
