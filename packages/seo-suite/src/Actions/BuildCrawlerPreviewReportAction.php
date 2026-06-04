<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\SeoSuite\Data\AiDiscoveryRenderContextData;
use Capell\SeoSuite\Data\CrawlerPreviewData;
use Capell\SeoSuite\Support\PublicOutputLeakScanner;
use Capell\SiteDiscovery\Actions\DiscoverPublicUrlsAction;
use Capell\SiteDiscovery\Data\DiscoverableUrlData;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Support\Str;
use JsonException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static list<CrawlerPreviewData> run(Site $site, Language $language, ?Page $page = null, string $crawlerUserAgent = 'CapellCrawlerPreview')
 */
final class BuildCrawlerPreviewReportAction
{
    use AsAction;

    private const string DefaultCrawlerUserAgent = 'CapellCrawlerPreview';

    /**
     * @return list<CrawlerPreviewData>
     *
     * @throws JsonException
     */
    public function handle(
        Site $site,
        Language $language,
        ?Page $page = null,
        string $crawlerUserAgent = self::DefaultCrawlerUserAgent,
    ): array {
        $site->loadMissing(['siteDomain', 'translation']);
        $context = new AiDiscoveryRenderContextData(
            site: $site,
            language: $language,
            siteDomain: $site->siteDomain instanceof SiteDomain ? $site->siteDomain : null,
            page: $page,
        );

        $previews = [
            $this->preview(
                key: 'robots_txt',
                path: '/robots.txt',
                contentType: 'text/plain',
                crawlerUserAgent: $crawlerUserAgent,
                content: BuildRobotsTxtAction::run($site),
            ),
            $this->preview(
                key: 'sitemap_xml',
                path: (string) config('capell.sitemap.xml_path', '/sitemap-xml'),
                contentType: 'application/xml',
                crawlerUserAgent: $crawlerUserAgent,
                content: $this->sitemapXml($site, $language, $context->siteDomain),
            ),
            $this->preview(
                key: 'llms_txt',
                path: '/llms.txt',
                contentType: 'text/markdown',
                crawlerUserAgent: $crawlerUserAgent,
                content: GenerateLlmsTxtAction::run($context),
            ),
            $this->preview(
                key: 'llms_full_txt',
                path: '/llms-full.txt',
                contentType: 'text/markdown',
                crawlerUserAgent: $crawlerUserAgent,
                content: GenerateLlmsFullTxtAction::run($context),
            ),
        ];

        if ($page instanceof Page) {
            $page->loadMissing([
                'pageUrl',
                'translation' => fn (BuilderContract $query): BuilderContract => $query->where('language_id', $language->getKey()),
                'translations.language',
            ]);

            $previews[] = $this->preview(
                key: 'page_markdown',
                path: $this->pageMarkdownPath($page),
                contentType: 'text/markdown',
                crawlerUserAgent: $crawlerUserAgent,
                content: GeneratePageMarkdownAction::run($context, $page),
            );

            $previews[] = $this->preview(
                key: 'schema_json',
                path: $this->schemaPath($page),
                contentType: 'application/ld+json',
                crawlerUserAgent: $crawlerUserAgent,
                content: $this->schemaJson($page, $site, $language),
            );
        }

        return $previews;
    }

    private function preview(
        string $key,
        string $path,
        string $contentType,
        string $crawlerUserAgent,
        string $content,
    ): CrawlerPreviewData {
        $warnings = $content === ''
            ? [__('capell-seo-suite::generic.crawler_preview_empty_output')]
            : $this->leakWarnings($content);

        return new CrawlerPreviewData(
            key: $key,
            path: $path,
            contentType: $contentType,
            crawlerUserAgent: $crawlerUserAgent,
            status: $warnings === [] ? 'ok' : 'warn',
            summary: $warnings === []
                ? __('capell-seo-suite::generic.crawler_preview_ready')
                : implode(' ', $warnings),
            byteSize: strlen($content),
            contentHash: hash('sha256', $content),
            preview: Str::limit($content, 500, '...'),
            warnings: $warnings,
        );
    }

    /**
     * @return list<string>
     */
    private function leakWarnings(string $content): array
    {
        $matches = (new PublicOutputLeakScanner)->labels($content);

        if ($matches === []) {
            return [];
        }

        return [__('capell-seo-suite::generic.crawler_preview_public_leak_warning', [
            'matches' => implode(', ', array_values(array_unique($matches))),
        ])];
    }

    private function pageMarkdownPath(Page $page): string
    {
        $path = $this->pagePath($page);

        if ($path === '/') {
            return '/index.md';
        }

        return rtrim($path, '/') . '.md';
    }

    private function schemaPath(Page $page): string
    {
        return $this->pagePath($page) . '#schema';
    }

    private function pagePath(Page $page): string
    {
        $fullUrl = $page->pageUrl?->full_url;

        if (! is_string($fullUrl) || trim($fullUrl) === '') {
            return '/pages/' . $page->getKey();
        }

        $path = parse_url($fullUrl, PHP_URL_PATH);

        return is_string($path) && $path !== '' ? $path : '/';
    }

    /**
     * @throws JsonException
     */
    private function schemaJson(Page $page, Site $site, Language $language): string
    {
        $schemas = ResolvePageStructuredDataAction::run($page, $site, $language);

        if ($schemas === []) {
            return '';
        }

        return json_encode($schemas, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
    }

    private function sitemapXml(Site $site, Language $language, ?SiteDomain $siteDomain): string
    {
        $urls = DiscoverPublicUrlsAction::run($site, $language, true, $siteDomain);

        if ($urls->isEmpty()) {
            return '';
        }

        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($urls as $url) {
            if (! $url instanceof DiscoverableUrlData) {
                continue;
            }

            $lines[] = '  <url>';
            $lines[] = '    <loc>' . e($url->loc) . '</loc>';

            if ($url->lastModified instanceof CarbonInterface) {
                $lines[] = '    <lastmod>' . e($url->lastModified->toDateString()) . '</lastmod>';
            }

            if ($url->changeFrequency !== null && $url->changeFrequency !== '') {
                $lines[] = '    <changefreq>' . e($url->changeFrequency) . '</changefreq>';
            }

            if ($url->priority !== null && $url->priority !== '') {
                $lines[] = '    <priority>' . e($url->priority) . '</priority>';
            }

            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines) . "\n";
    }
}
