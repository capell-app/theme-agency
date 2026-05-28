<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Enums\UrlTypeEnum;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Data\PageSpeedAuditTargetData;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class ResolvePageSpeedAuditTargetsAction
{
    use AsAction;

    /**
     * @return list<PageSpeedAuditTargetData>
     */
    public function handle(?int $siteId = null, ?int $languageId = null, ?int $pageId = null, ?int $limit = null): array
    {
        $targets = [];

        $this->query($siteId, $languageId, $pageId)
            ->chunkById(50, function (EloquentCollection $pages) use (&$targets, $languageId, $limit): bool {
                foreach ($pages as $page) {
                    if (! $page instanceof Page) {
                        continue;
                    }

                    foreach ($this->targetsForPage($page, $languageId) as $target) {
                        $targets[] = $target;

                        if ($limit !== null && count($targets) >= $limit) {
                            return false;
                        }
                    }
                }

                return true;
            });

        return $targets;
    }

    /**
     * @return Builder<Page>
     */
    private function query(?int $siteId, ?int $languageId, ?int $pageId): Builder
    {
        return Page::query()
            ->publishedDate()
            ->when($siteId !== null, fn (Builder $query): Builder => $query->where('site_id', $siteId))
            ->when($pageId !== null, fn (Builder $query): Builder => $query->whereKey($pageId))
            ->whereHas('site', fn (BuilderContract $query): BuilderContract => $query->where('status', true))
            ->whereHas('pageUrls', function (BuilderContract $query) use ($languageId): BuilderContract {
                return $query
                    ->where('status', true)
                    ->whereNull('type')
                    ->when($languageId !== null, fn (BuilderContract $query): BuilderContract => $query->where('language_id', $languageId));
            })
            ->with([
                'site',
                'pageUrls' => function (BuilderContract $query) use ($languageId): BuilderContract {
                    return $query
                        ->where('status', true)
                        ->whereNull('type')
                        ->when($languageId !== null, fn (BuilderContract $query): BuilderContract => $query->where('language_id', $languageId))
                        ->with(['language', 'site', 'siteDomain'])
                        ->ordered();
                },
            ])
            ->orderBy('id');
    }

    /**
     * @return list<PageSpeedAuditTargetData>
     */
    private function targetsForPage(Page $page, ?int $languageId): array
    {
        $targets = [];

        foreach ($page->pageUrls as $pageUrl) {
            if (! $pageUrl instanceof PageUrl || $pageUrl->type instanceof UrlTypeEnum) {
                continue;
            }

            if ($languageId !== null && (int) $pageUrl->language_id !== $languageId) {
                continue;
            }

            $site = $pageUrl->site;
            $language = $pageUrl->language;

            if (! $site instanceof Site || ! $language instanceof Language) {
                continue;
            }

            try {
                $url = $pageUrl->full_url;
            } catch (Throwable) {
                continue;
            }

            if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
                continue;
            }

            $targets[] = new PageSpeedAuditTargetData(
                page: $page,
                site: $site,
                language: $language,
                url: $url,
            );
        }

        return $targets;
    }
}
