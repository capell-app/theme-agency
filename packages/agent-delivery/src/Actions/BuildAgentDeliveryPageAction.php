<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Actions;

use Capell\AgentDelivery\Data\AgentDeliveryPageData;
use Capell\AgentDelivery\Support\AgentDeliveryRegistry;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Data\PublicPageFieldsData;
use Capell\Core\Models\Language;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static AgentDeliveryPageData run(Pageable $page, Site $site, Language $language, PublicPageFieldsData $fields)
 */
final class BuildAgentDeliveryPageAction
{
    use AsObject;

    public function __construct(private readonly AgentDeliveryRegistry $registry) {}

    /**
     * @param  Pageable<Model>  $page
     */
    public function handle(Pageable $page, Site $site, Language $language, PublicPageFieldsData $fields): AgentDeliveryPageData
    {
        $body = $this->plainText($fields->content);
        $metadata = array_merge(
            $this->publicMetadata($fields),
            $this->registry->metadata($page, $site, $language),
        );

        return new AgentDeliveryPageData(
            canonicalUrl: $this->canonicalUrl($page, $fields),
            url: $fields->url ?? '/',
            language: $language->locale ?? $language->code,
            alternates: $this->alternates($page),
            title: $fields->title,
            headings: $this->headings($fields->content, $fields->title),
            summary: $this->summary($fields, $body),
            body: $body,
            metadata: $metadata,
            references: array_values(array_merge(
                $this->references($fields->meta),
                $this->registry->references($page, $site, $language),
            )),
            publishedAt: $this->dateAttribute($page, 'visible_from'),
            lastUpdatedAt: $this->dateAttribute($page, 'updated_at'),
            relatedUrls: $this->registry->relatedUrls($page, $site, $language),
        );
    }

    private function canonicalUrl(Pageable $page, PublicPageFieldsData $fields): string
    {
        $pageUrl = $this->pageUrl($page);

        if ($pageUrl instanceof PageUrl) {
            return $pageUrl->full_url;
        }

        return $fields->url ?? '/';
    }

    /**
     * @return array<string, mixed>
     */
    private function publicMetadata(PublicPageFieldsData $fields): array
    {
        return array_filter([
            'description' => $this->metaString($fields->meta, 'description'),
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /**
     * @param  Pageable<Model>  $page
     * @return array<string, string>
     */
    private function alternates(Pageable $page): array
    {
        if (! $page instanceof Model) {
            return [];
        }

        $page->loadMissing('pageUrls.language', 'pageUrls.siteDomain');
        $resolvedSiteDomain = $this->pageUrl($page)?->siteDomain;

        /** @var iterable<int, PageUrl> $pageUrls */
        $pageUrls = $page->getRelation('pageUrls');
        $alternates = [];

        foreach ($pageUrls as $pageUrl) {
            if (! $pageUrl->status) {
                continue;
            }

            if ($pageUrl->language === null) {
                continue;
            }

            $locale = $pageUrl->language->locale ?? $pageUrl->language->code;
            $alternates[$locale] = $this->fullUrl($pageUrl, $resolvedSiteDomain);
        }

        return $alternates;
    }

    /**
     * @param  string|array<string, mixed>|null  $content
     * @return list<string>
     */
    private function headings(string|array|null $content, ?string $title): array
    {
        $headings = [];

        if (is_string($title) && trim($title) !== '') {
            $headings[] = trim($title);
        }

        if (is_string($content)) {
            preg_match_all('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/is', $content, $matches);

            foreach ($matches[1] as $heading) {
                $text = $this->plainText($heading);

                if ($text !== null && $text !== '') {
                    $headings[] = $text;
                }
            }
        }

        return array_values(array_unique($headings));
    }

    private function summary(PublicPageFieldsData $fields, ?string $body): ?string
    {
        $description = $this->metaString($fields->meta, 'description');

        if ($description !== null && $description !== '') {
            return $this->plainText($description);
        }

        if ($body === null || $body === '') {
            return null;
        }

        return Str::limit($body, 240, '');
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return list<array<string, string>>
     */
    private function references(array $meta): array
    {
        $references = $meta['references'] ?? [];

        if (! is_array($references)) {
            return [];
        }

        $safe = [];

        foreach ($references as $reference) {
            if (! is_array($reference)) {
                continue;
            }

            $url = $reference['url'] ?? null;
            if (! is_string($url)) {
                continue;
            }

            if (! $this->isPublicUrl($url)) {
                continue;
            }

            $safe[] = array_filter([
                'title' => is_string($reference['title'] ?? null) ? $this->plainText($reference['title']) : null,
                'url' => $url,
            ], static fn (?string $value): bool => $value !== null && $value !== '');
        }

        return $safe;
    }

    /**
     * @param  string|array<string, mixed>|null  $content
     */
    private function plainText(string|array|null $content): ?string
    {
        if ($content === null || is_array($content)) {
            return null;
        }

        $content = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $content) ?? $content;
        $content = preg_replace('/<[^>]+>/', ' ', $content) ?? $content;

        $text = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);

        return is_string($text) ? trim($text) : null;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function metaString(array $meta, string $key): ?string
    {
        $value = $meta[$key] ?? null;

        return is_scalar($value) ? (string) $value : null;
    }

    private function dateAttribute(Pageable $page, string $attribute): ?string
    {
        if (! $page instanceof Model) {
            return null;
        }

        $value = $page->getAttribute($attribute);

        return is_object($value) && method_exists($value, 'toIso8601String') ? $value->toIso8601String() : null;
    }

    private function isPublicUrl(string $url): bool
    {
        return str_starts_with($url, 'https://') || str_starts_with($url, 'http://');
    }

    private function fullUrl(PageUrl $pageUrl, ?SiteDomain $resolvedSiteDomain): string
    {
        if ($resolvedSiteDomain instanceof SiteDomain) {
            $siteDomain = $pageUrl->siteDomain;

            if ($siteDomain instanceof SiteDomain && $siteDomain->getRawOriginal('domain') === null) {
                $siteDomain->setAttribute('domain', $resolvedSiteDomain->domain);

                if ($siteDomain->getRawOriginal('scheme') === null || $siteDomain->getRawOriginal('scheme') === false) {
                    $siteDomain->setAttribute('scheme', $resolvedSiteDomain->scheme);
                }
            }
        }

        return $pageUrl->full_url;
    }

    /**
     * @param  Pageable<Model>  $page
     */
    private function pageUrl(Pageable $page): ?PageUrl
    {
        if (! $page instanceof Model) {
            return null;
        }

        $pageUrl = $page->getRelationValue('pageUrl');

        return $pageUrl instanceof PageUrl ? $pageUrl : null;
    }
}
