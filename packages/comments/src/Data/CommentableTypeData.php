<?php

declare(strict_types=1);

namespace Capell\Comments\Data;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;

class CommentableTypeData extends Data
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  Closure(Model): string  $labelResolver
     * @param  Closure(Model): ?string  $urlResolver
     * @param  Closure(Model): ?int  $siteResolver
     * @param  Closure(Model): bool  $visibilityResolver
     */
    public function __construct(
        public string $key,
        public string $modelClass,
        public Closure $labelResolver,
        public Closure $urlResolver,
        public Closure $siteResolver,
        public Closure $visibilityResolver,
    ) {}

    public function label(Model $model): string
    {
        return (string) ($this->labelResolver)($model);
    }

    public function url(Model $model): ?string
    {
        $url = ($this->urlResolver)($model);

        return is_string($url) && $url !== '' ? $url : null;
    }

    public function siteId(Model $model): ?int
    {
        $siteId = ($this->siteResolver)($model);

        return is_int($siteId) ? $siteId : null;
    }

    public function isVisible(Model $model): bool
    {
        return (bool) ($this->visibilityResolver)($model);
    }
}
