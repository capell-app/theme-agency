<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Actions;

use Capell\AgentDelivery\Data\AgentDeliveryPageIndexEntryData;
use Capell\Core\Enums\UrlTypeEnum;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static list<AgentDeliveryPageIndexEntryData> run(Site $site, Language $language)
 */
final class BuildAgentDeliveryPageIndexAction
{
    use AsObject;

    /**
     * @return list<AgentDeliveryPageIndexEntryData>
     */
    public function handle(Site $site, Language $language): array
    {
        $entries = PageUrl::query()
            ->where('site_id', $site->getKey())
            ->where('language_id', $language->getKey())
            ->where('status', true)
            ->where(function (BuilderContract $query): void {
                $query
                    ->whereNull('type')
                    ->orWhere('type', '!=', UrlTypeEnum::Redirect);
            })
            ->whereHasMorph(
                'pageable',
                [Page::class],
                fn (BuilderContract $query): BuilderContract => $this->publicPageQuery($query, $site),
            )
            ->with(['pageable', 'siteDomain'])
            ->orderBy('url')
            ->get()
            ->filter(fn (PageUrl $pageUrl): bool => $pageUrl->pageable instanceof Page && ! $this->isOptedOut($pageUrl->pageable))
            ->map(fn (PageUrl $pageUrl): AgentDeliveryPageIndexEntryData => $this->entry($pageUrl, $language))
            ->values()
            ->all();

        return array_values($entries);
    }

    private function publicPageQuery(BuilderContract $query, Site $site): BuilderContract
    {
        return $query
            ->where('site_id', $site->getKey())
            ->whereHas('type', fn (BuilderContract $typeQuery): BuilderContract => $typeQuery->enabled()->accessible())
            ->publishedDate();
    }

    private function entry(PageUrl $pageUrl, Language $language): AgentDeliveryPageIndexEntryData
    {
        $page = $pageUrl->pageable;
        $lastUpdatedAt = $page instanceof Model && is_object($page->getAttribute('updated_at')) && method_exists($page->getAttribute('updated_at'), 'toIso8601String')
            ? $page->getAttribute('updated_at')->toIso8601String()
            : null;
        $locale = $language->locale ?? $language->code;

        return new AgentDeliveryPageIndexEntryData(
            canonicalUrl: $pageUrl->full_url,
            url: $pageUrl->url,
            language: $locale,
            lastUpdatedAt: $lastUpdatedAt,
            manifestUrl: route('capell-agent-delivery.pages.manifest', ['url' => $pageUrl->url, 'locale' => $locale]),
            chunksUrl: route('capell-agent-delivery.pages.chunks', ['url' => $pageUrl->url, 'locale' => $locale]),
        );
    }

    private function isOptedOut(Page $page): bool
    {
        $meta = (array) $page->getAttribute('meta');
        $agentDelivery = $meta['agent_delivery'] ?? null;

        if (is_array($agentDelivery) && (($agentDelivery['enabled'] ?? null) === false || ($agentDelivery['exclude'] ?? null) === true)) {
            return true;
        }

        $robots = $meta['robots'] ?? [];

        if (is_string($robots)) {
            $robots = array_map(trim(...), explode(',', $robots));
        }

        if (is_array($robots)) {
            return collect($robots)
                ->filter(fn (mixed $value, mixed $key): bool => is_string($key) ? $value === true : is_string($value))
                ->map(fn (mixed $value, mixed $key): string => strtolower(is_string($key) ? $key : (string) $value))
                ->contains('noai');
        }

        return false;
    }
}
