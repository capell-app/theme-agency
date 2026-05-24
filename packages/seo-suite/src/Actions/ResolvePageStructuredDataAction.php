<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<array-key, mixed> run(Page $page, Site $site, Language $language)
 */
final class ResolvePageStructuredDataAction
{
    use AsAction;

    private const string StructuredDataKey = 'structured_data';

    /**
     * @return list<array<string, mixed>>
     */
    public function handle(Page $page, Site $site, Language $language): array
    {
        $page->loadMissing(['pageUrl', 'translation', 'translations.language']);
        $site->loadMissing(['siteDomain', 'translation']);

        return collect([
            $this->siteSchema($site, $language),
            ...$this->configuredSchemas($site->meta ?? [], $site),
            PageMetaSchemaAction::run($page, $site, $language),
            ...$this->breadcrumbSchemas($page, $site, $language),
            ...$this->configuredSchemas($page->meta ?? [], $site),
            ...$this->configuredSchemas($page->translation->meta ?? [], $site),
        ])
            ->filter(fn (array $schema): bool => $schema !== [])
            ->map(fn (array $schema): array => $this->withContext($this->normalizeUrls($schema, $site)))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function siteSchema(Site $site, Language $language): array
    {
        if ($site->siteDomain === null) {
            return [];
        }

        return SiteMetaSchemaAction::run($site, $language);
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return list<array<string, mixed>>
     */
    private function configuredSchemas(array $meta, Site $site): array
    {
        $schemas = $meta[self::StructuredDataKey] ?? [];

        if (! is_array($schemas)) {
            return [];
        }

        if (Arr::isAssoc($schemas)) {
            $schemas = [$schemas];
        }

        return collect($schemas)
            ->filter(fn (mixed $schema): bool => is_array($schema) && $schema !== [])
            ->map(fn (array $schema): array => $this->normalizeUrls($schema, $site))
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function breadcrumbSchemas(Page $page, Site $site, Language $language): array
    {
        $breadcrumbs = BreadcrumbsSchemaAction::run($page, $site, $language);

        if ($breadcrumbs !== []) {
            return $breadcrumbs;
        }

        $pageUrl = $page->pageUrl?->full_url;

        if (! is_string($pageUrl) || $pageUrl === '') {
            return [];
        }

        $siteUrl = $site->siteDomain?->full_url;
        $siteName = $site->translation->title ?? $site->name;

        return [[
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_filter([
                is_string($siteUrl) && $siteUrl !== '' ? [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => $siteName,
                    'item' => $siteUrl,
                ] : null,
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $page->translation->title ?? $page->name,
                    'item' => $pageUrl,
                ],
            ])),
        ]];
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private function withContext(array $schema): array
    {
        if (isset($schema['@context'])) {
            return $schema;
        }

        return ['@context' => 'https://schema.org', ...$schema];
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private function normalizeUrls(array $schema, Site $site): array
    {
        foreach ($schema as $key => $value) {
            if (is_array($value)) {
                $schema[$key] = Arr::isAssoc($value)
                    ? $this->normalizeUrls($value, $site)
                    : array_map(fn (mixed $item): mixed => is_array($item) ? $this->normalizeUrls($item, $site) : $item, $value);

                continue;
            }

            if (is_string($key) && in_array($key, ['url', 'logo', 'image', 'item'], true) && is_string($value)) {
                $schema[$key] = $this->absoluteUrl($value, $site);
            }
        }

        return $schema;
    }

    private function absoluteUrl(string $value, Site $site): string
    {
        if ($value === '' || Str::startsWith($value, ['http://', 'https://', '#'])) {
            return $value;
        }

        $siteUrl = $site->siteDomain?->full_url;

        if (! is_string($siteUrl) || $siteUrl === '') {
            return $value;
        }

        return rtrim($siteUrl, '/') . '/' . ltrim($value, '/');
    }
}
