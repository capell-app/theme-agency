<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Actions;

use Capell\AgentDelivery\Data\ResolvedAgentDeliveryPageData;
use Capell\Core\Actions\LoadSiteDomainFromUrlAction;
use Capell\Core\Actions\ResolvePublicPageByUrlAction;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static ResolvedAgentDeliveryPageData run(Request $request)
 */
final class ResolveAgentDeliveryPageAction
{
    use AsObject;

    private ?SiteDomain $resolvedSiteDomain = null;

    private ?string $resolvedUrlPath = null;

    public function handle(Request $request): ResolvedAgentDeliveryPageData
    {
        $site = $this->resolveSite($request);

        if (! $site instanceof Site) {
            return new ResolvedAgentDeliveryPageData(null, null, null, null);
        }

        $language = $this->resolveLanguage($site, $request);

        if (! $language instanceof Language) {
            return new ResolvedAgentDeliveryPageData(null, $site, null, null);
        }

        $resolution = ResolvePublicPageByUrlAction::run(
            site: $site,
            language: $language,
            url: $this->resolvedUrlPath ?? $this->queryString($request, 'url', '/'),
        );

        if (! $resolution->found() || $resolution->page === null) {
            return new ResolvedAgentDeliveryPageData(null, $site, $language, null);
        }

        if ($this->isOptedOut($resolution->page, $resolution->fields->meta)) {
            return new ResolvedAgentDeliveryPageData(null, $site, $language, null);
        }

        $this->hydrateAlternateUrls($resolution->page);

        $delivery = BuildAgentDeliveryPageAction::run(
            page: $resolution->page,
            site: $site,
            language: $language,
            fields: $resolution->fields,
        );

        return new ResolvedAgentDeliveryPageData($resolution->page, $site, $language, $delivery);
    }

    private function resolveSite(Request $request): ?Site
    {
        $publicPageUrl = $this->publicPageUrl($request);
        $resolved = LoadSiteDomainFromUrlAction::run($publicPageUrl, $this->candidateSitesForUrl($publicPageUrl));
        $siteDomain = is_array($resolved) ? ($resolved[0] ?? null) : null;
        $urlPath = is_array($resolved) ? ($resolved[1] ?? null) : null;

        if (! $siteDomain instanceof SiteDomain) {
            return null;
        }

        $this->resolvedSiteDomain = $siteDomain;
        $this->resolvedUrlPath = is_string($urlPath) && $urlPath !== '' ? $urlPath : null;

        $site = $siteDomain->site;
        $site->setRelation('siteDomains', collect([$siteDomain]));

        return $site;
    }

    private function resolveLanguage(Site $site, Request $request): ?Language
    {
        $requestedLocale = $this->queryString($request, 'locale', '');

        if ($requestedLocale !== '') {
            $language = Language::query()
                ->where('locale', $requestedLocale)
                ->orWhere('code', $requestedLocale)
                ->first();

            if ($language instanceof Language) {
                return $language;
            }
        }

        $domainLanguage = $this->resolvedSiteDomain?->language;

        if ($domainLanguage instanceof Language) {
            return $domainLanguage;
        }

        $siteLanguageId = $site->getAttribute('language_id');

        if (is_int($siteLanguageId)) {
            $siteLanguage = $site->relationLoaded('language')
                ? $site->language
                : Language::query()->whereKey($siteLanguageId)->first();

            if ($siteLanguage instanceof Language) {
                return $siteLanguage;
            }
        }

        return Language::query()->orderBy((new Language)->getKeyName())->first();
    }

    private function queryString(Request $request, string $key, string $default): string
    {
        $value = $request->query($key);

        if (! is_string($value) || trim($value) === '') {
            return $default;
        }

        return $value;
    }

    private function publicPageUrl(Request $request): string
    {
        return rtrim($request->getSchemeAndHttpHost(), '/') . '/' . ltrim($this->queryString($request, 'url', '/'), '/');
    }

    /**
     * @return Collection<int, Site>
     */
    private function candidateSitesForUrl(string $url): Collection
    {
        $parts = parse_url($url);
        $host = is_string($parts['host'] ?? null) ? $parts['host'] : null;
        $scheme = is_string($parts['scheme'] ?? null) ? $parts['scheme'] : 'https';
        $exactHostSites = $this->candidateSitesForDomain($host, $scheme, includeWildcardDomains: true);

        if ($exactHostSites->isNotEmpty()) {
            return $exactHostSites;
        }

        return $this->candidateSitesForDomain(null, $scheme, includeWildcardDomains: false);
    }

    /**
     * @return Collection<int, Site>
     */
    private function candidateSitesForDomain(?string $host, string $scheme, bool $includeWildcardDomains): Collection
    {
        return Site::query()
            ->with([
                'language',
                'siteDomains' => fn (BuilderContract $query): BuilderContract => $this->candidateSiteDomainQuery($query, $host, $scheme, includeWildcardDomains: $includeWildcardDomains)
                    ->with('language'),
            ])
            ->whereHas('siteDomains', fn (BuilderContract $query): BuilderContract => $this->candidateSiteDomainQuery($query, $host, $scheme, includeWildcardDomains: $includeWildcardDomains))
            ->limit($this->candidateSiteLimit())
            ->get();
    }

    private function candidateSiteLimit(): int
    {
        $limit = config('capell-agent-delivery.public_pages.max_candidate_sites', 50);

        if (is_int($limit)) {
            return $limit > 0 ? $limit : 50;
        }

        return is_numeric($limit) && (int) $limit > 0 ? (int) $limit : 50;
    }

    private function candidateSiteDomainQuery(BuilderContract $query, ?string $host, string $scheme, bool $includeWildcardDomains): BuilderContract
    {
        return $query
            ->where(function (BuilderContract $query) use ($host, $includeWildcardDomains): void {
                if ($host === null) {
                    $query->whereNull('domain');

                    return;
                }

                $query->where('domain', $host);

                if ($includeWildcardDomains) {
                    $query->orWhereNull('domain');
                }
            })
            ->where(function (BuilderContract $query) use ($scheme): void {
                $query
                    ->whereNull('scheme')
                    ->orWhere('scheme', false)
                    ->orWhere('scheme', $scheme);
            });
    }

    /**
     * @param  Pageable<Model>  $page
     * @param  array<string, mixed>  $translationMeta
     */
    private function isOptedOut(Pageable $page, array $translationMeta): bool
    {
        $pageMeta = $page instanceof Model ? (array) $page->getAttribute('meta') : [];
        if ($this->metaOptsOut($pageMeta)) {
            return true;
        }

        return $this->metaOptsOut($translationMeta);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function metaOptsOut(array $meta): bool
    {
        $agentDelivery = $meta['agent_delivery'] ?? null;

        if (is_array($agentDelivery) && (($agentDelivery['enabled'] ?? null) === false || ($agentDelivery['exclude'] ?? null) === true)) {
            return true;
        }

        $robots = $meta['robots'] ?? [];

        if (is_string($robots)) {
            $robots = array_map(trim(...), explode(',', $robots));
        }

        if (! is_array($robots)) {
            return false;
        }

        return collect($robots)
            ->filter(fn (mixed $value, mixed $key): bool => is_string($key) ? $value === true : is_string($value))
            ->map(fn (mixed $value, mixed $key): string => strtolower(is_string($key) ? $key : (string) $value))
            ->contains('noai');
    }

    /**
     * @param  Pageable<Model>  $page
     */
    private function hydrateAlternateUrls(Pageable $page): void
    {
        if (! $page instanceof Model) {
            return;
        }

        $page->loadMissing('pageUrls.language', 'pageUrls.siteDomain');
    }
}
