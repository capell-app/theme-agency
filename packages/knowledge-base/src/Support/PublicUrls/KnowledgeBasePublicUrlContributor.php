<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Support\PublicUrls;

use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleVersion;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Capell\KnowledgeBase\Providers\KnowledgeBaseServiceProvider;
use Capell\KnowledgeBase\Support\KnowledgeBasePublicPath;
use Capell\SiteDiscovery\Contracts\PublicUrlContributor;
use Capell\SiteDiscovery\Data\PublicUrlData;
use Capell\SiteDiscovery\Enums\PublicUrlContentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class KnowledgeBasePublicUrlContributor implements PublicUrlContributor
{
    /**
     * @return Collection<int, PublicUrlData>
     */
    public function publicUrls(): Collection
    {
        return SiteDomain::query()
            ->with(['site', 'language'])
            ->get()
            ->filter(fn (SiteDomain $domain): bool => $domain->site instanceof Site && $domain->language instanceof Language)
            ->flatMap(fn (SiteDomain $domain): Collection => $this->publicUrlsForDomain($domain))
            ->unique(fn (PublicUrlData $url): string => $this->modelKey($url->site) . '|' . $this->modelKey($url->language) . '|' . $url->canonicalUrl)
            ->values();
    }

    /**
     * @return Collection<int, PublicUrlData>
     */
    private function publicUrlsForDomain(SiteDomain $domain): Collection
    {
        $site = $domain->site;
        $language = $domain->language;

        if (! $site instanceof Site || ! $language instanceof Language) {
            return collect();
        }

        return KnowledgeBaseArticle::query()
            ->where('status', KnowledgeBaseArticleStatus::Published->value)
            ->whereNotNull('published_at')
            ->whereNotNull('current_version_id')
            ->whereHas('collection', fn (Builder $query): Builder => $query->where('is_public', true))
            ->with(['collection', 'currentVersion'])
            ->orderBy('title')
            ->get()
            ->filter(fn (KnowledgeBaseArticle $article): bool => $article->collection instanceof KnowledgeBaseCollection && $article->currentVersion instanceof KnowledgeBaseArticleVersion)
            ->map(fn (KnowledgeBaseArticle $article): PublicUrlData => new PublicUrlData(
                canonicalUrl: $this->canonicalUrl($domain, $article),
                sourcePackage: KnowledgeBaseServiceProvider::$packageName,
                site: $site,
                language: $language,
                routeName: 'capell-knowledge-base.article',
                lastModified: $article->currentVersion->published_at ?? $article->published_at,
                contentType: PublicUrlContentType::Article,
                isSitemapEligible: true,
                isAiDiscoveryEligible: true,
                priority: '0.7',
                changeFrequency: 'weekly',
                title: $article->currentVersion->title ?? $article->title,
            ))
            ->values();
    }

    private function canonicalUrl(SiteDomain $domain, KnowledgeBaseArticle $article): string
    {
        return rtrim($this->stringValue($domain->full_url), '/') . KnowledgeBasePublicPath::forArticle($article);
    }

    private function modelKey(Site|Language $model): string
    {
        $key = $model->getKey();

        return is_scalar($key) ? (string) $key : '';
    }

    private function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
