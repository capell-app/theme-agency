<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Actions;

use Capell\Core\Models\PageUrl;
use Capell\HtmlCache\Models\CachedModelUrl;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsObject;

final class BuildAuthoringBannerContextAction
{
    use AsObject;

    /**
     * @return array{
     *     page: string,
     *     url: string,
     *     updated: string|null,
     *     updated_by: string|null,
     *     html_cached: bool,
     *     cached_at: string|null
     * }
     */
    public function handle(PageUrl $pageUrl): array
    {
        $pageable = $pageUrl->pageable;

        if ($pageable instanceof Model && method_exists($pageable, 'editor')) {
            $pageable->loadMissing('editor');
        }

        $cachedUrl = CachedModelUrl::query()
            ->select(['id', 'cached_at'])
            ->where('url_hash', CachedModelUrl::hashUrl($pageUrl->full_url))
            ->latest('cached_at')
            ->first();

        return [
            'page' => $this->pageLabel($pageUrl, $pageable),
            'url' => $pageUrl->url,
            'updated' => $this->dateValue($pageable?->getAttribute('updated_at')),
            'updated_by' => $this->updatedBy($pageable),
            'html_cached' => $cachedUrl instanceof CachedModelUrl,
            'cached_at' => $this->dateValue($cachedUrl?->cached_at),
        ];
    }

    private function pageLabel(PageUrl $pageUrl, ?Model $pageable): string
    {
        foreach (['title', 'name', 'label'] as $attribute) {
            $value = $pageable?->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return $pageUrl->url === '/' ? '/' : trim($pageUrl->url, '/');
    }

    private function updatedBy(?Model $pageable): ?string
    {
        if (! $pageable instanceof Model || ! $pageable->relationLoaded('editor')) {
            return null;
        }

        $editor = $pageable->getRelation('editor');
        $name = data_get($editor, 'name');

        return is_string($name) && $name !== '' ? $name : null;
    }

    private function dateValue(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->diffForHumans();
        }

        return null;
    }
}
