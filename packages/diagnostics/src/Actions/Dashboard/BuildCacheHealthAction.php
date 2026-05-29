<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\Dashboard;

use Capell\Core\Enums\UrlTypeEnum;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Diagnostics\Data\Dashboard\CacheHealthData;
use Capell\HtmlCache\Actions\BuildHtmlCacheEligibilityReportAction;
use Capell\HtmlCache\Models\CachedModelUrl;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static CacheHealthData run(Site $site)
 */
final class BuildCacheHealthAction
{
    use AsAction;

    public function handle(Site $site): CacheHealthData
    {
        /** @var class-string<PageUrl> $model */
        $model = PageUrl::class;

        $query = $model::query()
            ->select(['id', 'site_id', 'language_id', 'url', 'type', 'status', 'updated_at'])
            ->with(['siteDomain'])
            ->where('site_id', $site->id)
            ->enabled()
            ->where(function (Builder $query): void {
                $query->whereNull('type')
                    ->orWhere('type', '!=', UrlTypeEnum::Redirect->value);
            });

        $totalEnabledUrls = (clone $query)->count();

        $cachedCount = 0;
        $staleCount = 0;
        $missingCount = 0;
        $newestTimestamp = null;

        foreach ($query->lazyById(500) as $pageUrl) {
            $cachedUrl = CachedModelUrl::query()
                ->where('cacheable_type', $pageUrl->getMorphClass())
                ->where('cacheable_id', $pageUrl->getKey())
                ->latest('cached_at')
                ->first();

            if (! $cachedUrl instanceof CachedModelUrl || $cachedUrl->cached_at === null) {
                $missingCount++;

                continue;
            }

            $cachedAtTimestamp = $cachedUrl->cached_at->getTimestamp();
            if ($newestTimestamp === null || $cachedAtTimestamp > $newestTimestamp) {
                $newestTimestamp = $cachedAtTimestamp;
            }

            $pageUpdatedAt = $pageUrl->updated_at;

            if ($pageUpdatedAt !== null && $cachedAtTimestamp < $pageUpdatedAt->getTimestamp()) {
                $staleCount++;
            } else {
                $cachedCount++;
            }
        }

        $lastWarmedAt = $newestTimestamp !== null
            ? CarbonImmutable::createFromTimestamp($newestTimestamp)->toIso8601String()
            : null;

        return new CacheHealthData(
            totalEnabledUrls: $totalEnabledUrls,
            cachedCount: $cachedCount,
            staleCount: $staleCount,
            missingCount: $missingCount,
            lastWarmedAt: $lastWarmedAt,
            siteId: $site->id,
            siteName: $site->name,
            eligibilityReports: $this->eligibilityReports($site),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function eligibilityReports(Site $site): array
    {
        return PageUrl::query()
            ->with(['siteDomain', 'pageable'])
            ->where('site_id', $site->id)
            ->enabled()
            ->limit(5)
            ->get()
            ->map(function (PageUrl $pageUrl): array {
                $siteDomain = $pageUrl->siteDomain;

                if ($siteDomain === null) {
                    return BuildHtmlCacheEligibilityReportAction::run(
                        Request::create($pageUrl->url, \Symfony\Component\HttpFoundation\Request::METHOD_GET),
                        pageUrl: $pageUrl,
                    )->toArray();
                }

                $url = rtrim($siteDomain->full_url, '/') . $pageUrl->url;

                return BuildHtmlCacheEligibilityReportAction::run(
                    Request::create($url, \Symfony\Component\HttpFoundation\Request::METHOD_GET),
                    pageUrl: $pageUrl,
                )->toArray();
            })
            ->filter()
            ->values()
            ->all();
    }
}
