<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Frontend\Actions\ResolvePageRobotsDirectivesAction;
use Capell\SeoSuite\Data\SeoSuiteDoctorCheckData;
use Capell\SeoSuite\Enums\RobotsDirectiveEnum;
use Capell\SeoSuite\Http\Controllers\LlmsFullTxtController;
use Capell\SeoSuite\Http\Controllers\LlmsTxtController;
use Capell\SeoSuite\Http\Controllers\PageMarkdownController;
use Capell\SeoSuite\Http\Controllers\RobotsTxtController;
use Capell\SeoSuite\Models\AiDiscoveryPageProfile;
use Capell\SeoSuite\Models\AiDiscoverySiteProfile;
use Capell\SeoSuite\Models\AiDiscoverySnapshot;
use Capell\SeoSuite\Providers\SeoSuiteServiceProvider;
use Capell\SeoSuite\Settings\AIOrchestratorSettings;
use Capell\SeoSuite\Settings\SeoSuiteSettings;
use Capell\SiteDiscovery\Actions\DiscoverPublicPagesAction;
use Capell\SiteDiscovery\Data\DiscoverablePageData;
use Capell\SiteDiscovery\Providers\SiteDiscoveryServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

/**
 * @method static Collection<int, SeoSuiteDoctorCheckData> run(?string $baseUrl = null, bool $includeHttp = true)
 */
final class BuildSeoSuiteDoctorReportAction
{
    use AsAction;

    /** @var array<string, class-string> */
    private const array OWNED_ROUTES = [
        'llms.txt' => LlmsTxtController::class,
        'llms-full.txt' => LlmsFullTxtController::class,
        'robots.txt' => RobotsTxtController::class,
        'index.md' => PageMarkdownController::class,
        '{url}.md' => PageMarkdownController::class,
    ];

    /** @var array<string, string> */
    private const array ENDPOINT_CONTENT_TYPES = [
        '/robots.txt' => 'text/plain',
        '/llms.txt' => 'text/markdown',
        '/llms-full.txt' => 'text/markdown',
        '/index.md' => 'text/markdown',
        '/sitemap-xml' => 'xml',
        '/sitemap.xml' => 'xml',
    ];

    public function __construct(
        private readonly Router $router,
        private readonly HttpFactory $http,
    ) {}

    /**
     * @return Collection<int, SeoSuiteDoctorCheckData>
     */
    public function handle(?string $baseUrl = null, bool $includeHttp = true): Collection
    {
        $checks = collect();

        $this->checkPackageStatus($checks);
        $this->checkSettings($checks);
        $this->checkRouteOwnership($checks);
        $this->checkAiDiscoveryCoverage($checks);
        $this->checkSitemapConfiguration($checks);

        if ($includeHttp) {
            $this->checkEndpointResponses($checks, $baseUrl);
        }

        return $checks->values();
    }

    /**
     * @param  Collection<int, SeoSuiteDoctorCheckData>  $checks
     */
    private function checkPackageStatus(Collection $checks): void
    {
        $checks->push($this->check(
            'Packages',
            CapellCore::isPackageInstalled(SeoSuiteServiceProvider::$packageName) ? 'ok' : 'fail',
            'SEO Suite package installation status',
            SeoSuiteServiceProvider::$packageName,
        ));

        $checks->push($this->check(
            'Packages',
            class_exists(SiteDiscoveryServiceProvider::class) && CapellCore::isPackageInstalled(SiteDiscoveryServiceProvider::$packageName) ? 'ok' : 'warn',
            'Site Discovery package installation status',
            class_exists(SiteDiscoveryServiceProvider::class) ? SiteDiscoveryServiceProvider::$packageName : 'Provider class missing',
        ));
    }

    /**
     * @param  Collection<int, SeoSuiteDoctorCheckData>  $checks
     */
    private function checkSettings(Collection $checks): void
    {
        try {
            $settings = resolve(SeoSuiteSettings::class);
            $checks->push($this->check(
                'Settings',
                $settings->ai_discovery_default_enabled ? 'ok' : 'warn',
                'AI Discovery default profile setting',
                $settings->ai_discovery_default_enabled ? 'enabled' : 'disabled',
            ));
            $checks->push($this->check('Settings', 'ok', 'AI crawler policy preset', $settings->ai_discovery_crawler_policy));
        } catch (Throwable $throwable) {
            $checks->push($this->check('Settings', 'warn', 'SEO Suite settings could not be loaded', $throwable->getMessage()));
        }

        try {
            $settings = resolve(AIOrchestratorSettings::class);
            $enabled = collect([
                'page_content_generator' => $settings->page_content_generator,
                'page_title_suggestions' => $settings->page_title_suggestions,
                'ai_creator' => $settings->ai_creator,
            ])->filter(fn (bool $enabled): bool => $enabled);

            $checks->push($this->check(
                'Settings',
                $enabled->isNotEmpty() ? 'ok' : 'warn',
                'Enabled AI profile features',
                $enabled->keys()->implode(', ') ?: 'none enabled',
            ));
        } catch (Throwable $throwable) {
            $checks->push($this->check('Settings', 'warn', 'AI Orchestrator settings could not be loaded', $throwable->getMessage()));
        }
    }

    /**
     * @param  Collection<int, SeoSuiteDoctorCheckData>  $checks
     */
    private function checkRouteOwnership(Collection $checks): void
    {
        foreach (self::OWNED_ROUTES as $uri => $controller) {
            $routes = $this->routesForUri($uri);

            if ($routes->isEmpty()) {
                $checks->push($this->check('Routes', 'fail', sprintf('Missing route /%s', $uri)));

                continue;
            }

            $owners = $routes->map(fn (Route $route): string => $this->routeOwner($route))->unique()->values();
            $ownedByPackage = $owners->contains(fn (string $owner): bool => str_starts_with($owner, $controller));
            $isExpectedOwner = $ownedByPackage && $routes->count() === 1;
            $message = $isExpectedOwner
                ? sprintf('SEO Suite owns /%s', $uri)
                : sprintf('Route collision detected for /%s', $uri);

            $checks->push($this->check('Routes', $isExpectedOwner ? 'ok' : 'warn', $message, $owners->implode(' | ')));
        }
    }

    /**
     * @param  Collection<int, SeoSuiteDoctorCheckData>  $checks
     */
    private function checkAiDiscoveryCoverage(Collection $checks): void
    {
        if (! Schema::hasTable('ai_discovery_page_profiles')) {
            $checks->push($this->check('AI Discovery', 'warn', 'AI Discovery page profiles table is missing'));

            return;
        }

        $query = AiDiscoveryPageProfile::query();
        $included = (clone $query)->where('include_in_ai_index', true)->count();
        $excluded = (clone $query)->where('include_in_ai_index', false)->count();
        $excludedWithoutReason = (clone $query)
            ->where('include_in_ai_index', false)
            ->where(fn (Builder $builder): Builder => $builder->whereNull('exclude_reason')->orWhere('exclude_reason', ''))
            ->count();
        $missingSummaries = (clone $query)
            ->where('include_in_ai_index', true)
            ->where(fn (Builder $builder): Builder => $builder->whereNull('summary')->orWhere('summary', ''))
            ->count();
        $staleMarkdown = (clone $query)
            ->where('include_in_ai_index', true)
            ->where(fn (Builder $builder): Builder => $builder->whereNull('last_generated_at')->orWhereNull('generated_markdown'))
            ->count();

        $checks->push($this->check('AI Discovery', 'ok', 'Pages included in llms.txt', (string) $included));
        $checks->push($this->check('AI Discovery', $excluded > 0 ? 'warn' : 'ok', 'Pages excluded from llms.txt', (string) $excluded));
        $checks->push($this->check('AI Discovery', $excludedWithoutReason > 0 ? 'warn' : 'ok', 'Excluded pages missing reasons', (string) $excludedWithoutReason));
        $checks->push($this->check('AI Discovery', $missingSummaries > 0 ? 'warn' : 'ok', 'Included pages missing summaries', (string) $missingSummaries));
        $checks->push($this->check('AI Discovery', $staleMarkdown > 0 ? 'warn' : 'ok', 'Stale generated Markdown snapshots', (string) $staleMarkdown));
        $this->checkIncludedNoindexPages($checks);

        $this->checkAiDiscoverySiteProfiles($checks);
        $this->checkDiscoverablePagesMissingAiProfiles($checks);
        $this->checkSnapshots($checks);
    }

    /**
     * @param  Collection<int, SeoSuiteDoctorCheckData>  $checks
     */
    private function checkIncludedNoindexPages(Collection $checks): void
    {
        $includedNoindexPages = AiDiscoveryPageProfile::query()
            ->with(['page', 'language'])
            ->where('include_in_ai_index', true)
            ->get()
            ->filter(function (AiDiscoveryPageProfile $profile): bool {
                if (! $profile->page instanceof Page || ! $profile->language instanceof Language) {
                    return false;
                }

                return in_array(
                    RobotsDirectiveEnum::NoIndex->value,
                    ResolvePageRobotsDirectivesAction::run($profile->page, $profile->language),
                    true,
                );
            })
            ->count();

        $checks->push($this->check(
            'AI Discovery',
            $includedNoindexPages > 0 ? 'warn' : 'ok',
            'Noindex pages included in AI Discovery',
            (string) $includedNoindexPages,
        ));
    }

    /**
     * @param  Collection<int, SeoSuiteDoctorCheckData>  $checks
     */
    private function checkAiDiscoverySiteProfiles(Collection $checks): void
    {
        if (! Schema::hasTable('ai_discovery_site_profiles')) {
            $checks->push($this->check('AI Discovery', 'warn', 'AI Discovery site profiles table is missing'));

            return;
        }

        $disabledProfiles = AiDiscoverySiteProfile::query()
            ->where(function (Builder $builder): void {
                $builder
                    ->where('llms_txt_enabled', false)
                    ->orWhere('llms_full_txt_enabled', false)
                    ->orWhere('markdown_pages_enabled', false);
            })
            ->count();

        $checks->push($this->check(
            'AI Discovery',
            $disabledProfiles > 0 ? 'warn' : 'ok',
            'Site profiles with disabled AI discovery outputs',
            (string) $disabledProfiles,
        ));
    }

    /**
     * @param  Collection<int, SeoSuiteDoctorCheckData>  $checks
     */
    private function checkDiscoverablePagesMissingAiProfiles(Collection $checks): void
    {
        if (! Schema::hasTable('ai_discovery_site_profiles')) {
            return;
        }

        $missingProfiles = AiDiscoverySiteProfile::query()
            ->with(['site', 'language'])
            ->get()
            ->sum(function (AiDiscoverySiteProfile $profile): int {
                if (! $profile->site instanceof Site || ! $profile->language instanceof Language) {
                    return 0;
                }

                try {
                    $discoverablePageIds = DiscoverPublicPagesAction::run($profile->site, $profile->language)
                        ->map(fn (DiscoverablePageData $page): ?int => $page->page instanceof Page ? (int) $page->page->getKey() : null)
                        ->filter(fn (?int $pageId): bool => $pageId !== null)
                        ->values();
                } catch (Throwable) {
                    return 0;
                }

                if ($discoverablePageIds->isEmpty()) {
                    return 0;
                }

                $profiledPageIds = AiDiscoveryPageProfile::query()
                    ->where('site_id', $profile->site->getKey())
                    ->where('language_id', $profile->language->getKey())
                    ->whereIn('page_id', $discoverablePageIds->all())
                    ->pluck('page_id')
                    ->map(fn (int|string $pageId): int => (int) $pageId);

                return $discoverablePageIds->diff($profiledPageIds)->count();
            });

        $checks->push($this->check(
            'AI Discovery',
            $missingProfiles > 0 ? 'warn' : 'ok',
            'Site Discovery pages missing from AI Discovery profiles',
            (string) $missingProfiles,
        ));
    }

    /**
     * @param  Collection<int, SeoSuiteDoctorCheckData>  $checks
     */
    private function checkSnapshots(Collection $checks): void
    {
        if (! Schema::hasTable('ai_discovery_snapshots')) {
            return;
        }

        $expired = AiDiscoverySnapshot::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->count();

        $checks->push($this->check(
            'AI Discovery',
            $expired > 0 ? 'warn' : 'ok',
            'Expired generated output snapshots',
            (string) $expired,
        ));
    }

    /**
     * @param  Collection<int, SeoSuiteDoctorCheckData>  $checks
     */
    private function checkSitemapConfiguration(Collection $checks): void
    {
        $xmlPath = (string) config('capell.sitemap.xml_path', '/sitemap-xml');
        $checks->push($this->check('Sitemap', 'ok', 'Configured XML sitemap endpoint', $xmlPath));
        $checks->push($this->check(
            'Sitemap',
            $xmlPath === '/sitemap.xml' ? 'warn' : 'ok',
            'Static web-server interception risk',
            $xmlPath === '/sitemap.xml'
                ? 'Exact nginx/Apache exception required before generic *.xml static handlers.'
                : 'Low risk because the endpoint does not use a static file extension.',
        ));
    }

    /**
     * @param  Collection<int, SeoSuiteDoctorCheckData>  $checks
     */
    private function checkEndpointResponses(Collection $checks, ?string $baseUrl): void
    {
        $baseUrl = rtrim($baseUrl ?: (string) config('app.url'), '/');

        if ($baseUrl === '') {
            $checks->push($this->check('HTTP', 'warn', 'Endpoint HTTP checks skipped', 'No base URL configured.'));

            return;
        }

        foreach ($this->endpointContentTypes() as $path => $expectedContentType) {
            try {
                $response = $this->http->timeout(5)->withoutRedirecting()->get($baseUrl . $path);
            } catch (Throwable $exception) {
                $checks->push($this->check('HTTP', 'warn', sprintf('Could not request %s', $path), $exception->getMessage()));

                continue;
            }

            $contentTypeHeader = $response->header('Content-Type');
            $contentType = mb_strtolower(is_string($contentTypeHeader) ? $contentTypeHeader : '');
            $status = $response->status();
            $matchesContentType = str_contains($contentType, $expectedContentType);
            $isRedirect = $status >= 300 && $status < 400;
            $isLikelyStaticHandler = $status === 404 && (
                str_contains($contentType, 'text/html')
                || str_contains(mb_strtolower($response->body()), 'nginx')
                || str_contains(mb_strtolower($response->body()), 'apache')
            );

            $checks->push($this->check(
                'HTTP',
                $status === 200 && $matchesContentType ? 'ok' : 'warn',
                sprintf('Crawler endpoint %s returned HTTP %d', $path, $status),
                $this->httpDetail($response, $contentType, $expectedContentType, $isLikelyStaticHandler, $isRedirect),
            ));

            $this->checkCacheHeader($checks, $path, $response);
            $this->checkOutputLeaks($checks, $path, $response);
            $this->checkSitemapXml($checks, $path, $expectedContentType, $response);
        }
    }

    /**
     * @return array<string, string>
     */
    private function endpointContentTypes(): array
    {
        $endpoints = self::ENDPOINT_CONTENT_TYPES;
        $sampleMarkdownPath = $this->samplePageMarkdownPath();

        if ($sampleMarkdownPath !== null) {
            $endpoints[$sampleMarkdownPath] = 'text/markdown';
        }

        return $endpoints;
    }

    private function samplePageMarkdownPath(): ?string
    {
        if (! Schema::hasTable('ai_discovery_page_profiles')) {
            return null;
        }

        $profile = AiDiscoveryPageProfile::query()
            ->with('page.pageUrl')
            ->where('include_in_ai_index', true)
            ->whereNotNull('generated_markdown')
            ->first();

        $url = $profile?->page?->pageUrl?->full_url;

        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        $trimmedUrl = rtrim($url, '/');
        $path = $trimmedUrl !== '' ? parse_url($trimmedUrl, PHP_URL_PATH) : false;

        if (! is_string($path) || $path === '' || $path === '/') {
            return null;
        }

        return $path . '.md';
    }

    /**
     * @param  Collection<int, SeoSuiteDoctorCheckData>  $checks
     */
    private function checkCacheHeader(Collection $checks, string $path, Response $response): void
    {
        if (! $response->successful()) {
            return;
        }

        $cacheControl = $response->header('Cache-Control');
        $hasCacheHeader = is_string($cacheControl) && trim($cacheControl) !== '';

        $checks->push($this->check(
            'HTTP',
            $hasCacheHeader ? 'ok' : 'warn',
            sprintf('Crawler endpoint %s cache header', $path),
            $hasCacheHeader ? $cacheControl : 'missing Cache-Control header',
        ));
    }

    /**
     * @param  Collection<int, SeoSuiteDoctorCheckData>  $checks
     */
    private function checkOutputLeaks(Collection $checks, string $path, Response $response): void
    {
        if (! $response->successful()) {
            return;
        }

        $matches = $this->leakMatches($response->body());

        $checks->push($this->check(
            'Output',
            $matches === [] ? 'ok' : 'warn',
            sprintf('Generated output %s public leak scan', $path),
            $matches === [] ? 'no obvious admin/editor leaks found' : implode(', ', $matches),
        ));
    }

    /**
     * @return list<string>
     */
    private function leakMatches(string $content): array
    {
        $patterns = [
            '/admin' => ['/admin'],
            '/filament' => ['/filament'],
            'signed URL parameter' => ['signature='],
            'signed expiry parameter' => ['expires='],
            'Livewire directive' => ['wire:'],
            'Livewire internals' => ['livewire'],
            'field path' => ['field_path', 'field-path', 'fieldPath'],
            'model ID' => ['model_id', 'model-id', 'modelId'],
            'page ID' => ['page_id', 'page-id', 'pageId'],
            'editor metadata' => ['editor-only', 'capell-editor', 'data-editor'],
            'draft marker' => ['draft=true', 'status=draft', '/draft'],
            'unpublished marker' => ['unpublished=true', 'status=unpublished', '/unpublished'],
        ];

        $lowerContent = mb_strtolower($content);
        $matches = [];

        foreach ($patterns as $label => $needles) {
            $hasMatch = collect($needles)->contains(
                fn (string $needle): bool => str_contains($lowerContent, mb_strtolower($needle)),
            );

            if ($hasMatch) {
                $matches[] = $label;
            }
        }

        return array_values(array_unique($matches));
    }

    /**
     * @param  Collection<int, SeoSuiteDoctorCheckData>  $checks
     */
    private function checkSitemapXml(Collection $checks, string $path, string $expectedContentType, Response $response): void
    {
        if ($expectedContentType !== 'xml' || ! $response->successful()) {
            return;
        }

        $body = $response->body();
        $isValidXml = $this->isValidSitemapXml($body);
        $unsafeLocs = $this->unsafeSitemapLocs($body);

        $checks->push($this->check(
            'Sitemap',
            $isValidXml ? 'ok' : 'warn',
            sprintf('Sitemap endpoint %s XML validity', $path),
            $isValidXml ? 'valid sitemap XML' : 'invalid XML or unexpected sitemap root',
        ));

        $checks->push($this->check(
            'Sitemap',
            $unsafeLocs === [] ? 'ok' : 'warn',
            sprintf('Sitemap endpoint %s unsafe URL scan', $path),
            $unsafeLocs === [] ? 'no obvious private, admin, signed, or draft URLs found' : implode(', ', array_slice($unsafeLocs, 0, 10)),
        ));
    }

    /**
     * @return Collection<int, Route>
     */
    private function routesForUri(string $uri): Collection
    {
        return collect($this->router->getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => in_array('GET', $route->methods(), true) && $route->uri() === $uri)
            ->values();
    }

    private function routeOwner(Route $route): string
    {
        return $route->getActionName();
    }

    private function httpDetail(Response $response, string $contentType, string $expectedContentType, bool $isLikelyStaticHandler, bool $isRedirect): string
    {
        $detail = sprintf('Content-Type: %s; expected %s', $contentType !== '' ? $contentType : 'missing', $expectedContentType);

        if ($isRedirect) {
            $location = $response->header('Location');

            return sprintf('%s; redirect to %s', $detail, is_string($location) && $location !== '' ? $location : 'unknown location');
        }

        if ($isLikelyStaticHandler) {
            return $detail . '; possible nginx/Apache static handler interception';
        }

        return $detail;
    }

    private function isValidSitemapXml(string $content): bool
    {
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $xml = simplexml_load_string($content);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($xml === false || $errors !== []) {
            return false;
        }

        $rootName = $xml->getName();

        return $rootName === 'urlset' || $rootName === 'sitemapindex';
    }

    /**
     * @return list<string>
     */
    private function unsafeSitemapLocs(string $content): array
    {
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();
        $xml = simplexml_load_string($content);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($xml === false) {
            return [];
        }

        $locs = [];

        foreach ($xml->xpath('//*[local-name()="loc"]') ?: [] as $loc) {
            $url = trim((string) $loc);
            if ($url === '') {
                continue;
            }

            if ($this->leakMatches($url) === []) {
                continue;
            }

            $locs[] = $url;
        }

        return $locs;
    }

    private function check(string $area, string $status, string $message, ?string $detail = null): SeoSuiteDoctorCheckData
    {
        return new SeoSuiteDoctorCheckData($area, $status, $message, $detail);
    }
}
