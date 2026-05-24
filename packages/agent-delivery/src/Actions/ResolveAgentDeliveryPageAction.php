<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Actions;

use Capell\AgentDelivery\Data\ResolvedAgentDeliveryPageData;
use Capell\Core\Actions\LoadSiteDomainFromUrlAction;
use Capell\Core\Actions\ResolvePublicPageByUrlAction;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
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

        $language = $this->resolveLanguage($site);

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

        return $siteDomain->site;
    }

    private function resolveLanguage(Site $site): ?Language
    {
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
            ->whereHas('siteDomains', fn (BuilderContract $query): BuilderContract => $this->candidateSiteDomainQuery($query, $host, $scheme, includeWildcardDomains: false))
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
}
