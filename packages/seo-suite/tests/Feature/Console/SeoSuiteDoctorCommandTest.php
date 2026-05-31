<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Actions\BuildSeoSuiteDoctorReportAction;
use Capell\SeoSuite\Data\SeoSuiteDoctorCheckData;
use Capell\SeoSuite\Models\AiDiscoveryPageProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

it('reports route collisions for generated seo suite outputs', function (): void {
    Route::get('llms.txt', fn (): string => 'host override');

    $checks = BuildSeoSuiteDoctorReportAction::run(includeHttp: false);

    $routeCheck = $checks->first(fn (SeoSuiteDoctorCheckData $check): bool => str_contains($check->message, '/llms.txt'));

    expect($routeCheck)->not->toBeNull()
        ->and($routeCheck->status)->toBe('warn')
        ->and($routeCheck->message)->toBe('Route collision detected for /llms.txt')
        ->and($routeCheck->detail)->toContain('Closure');
});

it('checks generated endpoint status and content types', function (): void {
    Http::fake([
        'https://example.test/robots.txt' => Http::response("User-agent: *\nAllow: /\n", 200, ['Content-Type' => 'text/plain; charset=utf-8', 'Cache-Control' => 'public, max-age=300']),
        'https://example.test/llms.txt' => Http::response("# Example\n", 200, ['Content-Type' => 'text/markdown; charset=utf-8', 'Cache-Control' => 'public, max-age=300']),
        'https://example.test/llms-full.txt' => Http::response("# Example\n", 200, ['Content-Type' => 'text/markdown; charset=utf-8', 'Cache-Control' => 'public, max-age=300']),
        'https://example.test/index.md' => Http::response("# Home\n", 200, ['Content-Type' => 'text/markdown; charset=utf-8', 'Cache-Control' => 'public, max-age=300']),
        'https://example.test/sitemap-xml' => Http::response('<urlset />', 200, ['Content-Type' => 'application/xml', 'Cache-Control' => 'public, max-age=300']),
        'https://example.test/sitemap.xml' => Http::response('<html>nginx 404</html>', 404, ['Content-Type' => 'text/html']),
    ]);

    $checks = BuildSeoSuiteDoctorReportAction::run(baseUrl: 'https://example.test');

    expect($checks->firstWhere('message', 'Crawler endpoint /robots.txt returned HTTP 200')->status)->toBe('ok')
        ->and($checks->firstWhere('message', 'Crawler endpoint /robots.txt cache header')->status)->toBe('ok')
        ->and($checks->firstWhere('message', 'Generated output /llms.txt public leak scan')->status)->toBe('ok')
        ->and($checks->firstWhere('message', 'Sitemap endpoint /sitemap-xml XML validity')->status)->toBe('ok')
        ->and($checks->firstWhere('message', 'Crawler endpoint /sitemap.xml returned HTTP 404')->status)->toBe('warn')
        ->and($checks->firstWhere('message', 'Crawler endpoint /sitemap.xml returned HTTP 404')->detail)->toContain('possible nginx/Apache static handler interception');
});

it('flags redirects invalid sitemap xml unsafe sitemap urls and generated output leaks', function (): void {
    Http::fake([
        'https://bad.test/robots.txt' => Http::response('', 302, ['Location' => 'https://bad.test/robots']),
        'https://bad.test/llms.txt' => Http::response("[Admin](/admin)\n", 200, ['Content-Type' => 'text/markdown; charset=utf-8']),
        'https://bad.test/llms-full.txt' => Http::response("# Example\n", 200, ['Content-Type' => 'text/markdown; charset=utf-8', 'Cache-Control' => 'public, max-age=300']),
        'https://bad.test/index.md' => Http::response("# Home\n", 200, ['Content-Type' => 'text/markdown; charset=utf-8', 'Cache-Control' => 'public, max-age=300']),
        'https://bad.test/sitemap-xml' => Http::response('<not-xml', 200, ['Content-Type' => 'application/xml', 'Cache-Control' => 'public, max-age=300']),
        'https://bad.test/sitemap.xml' => Http::response('<urlset><url><loc>https://bad.test/admin?signature=abc</loc></url></urlset>', 200, ['Content-Type' => 'application/xml', 'Cache-Control' => 'public, max-age=300']),
    ]);

    $checks = BuildSeoSuiteDoctorReportAction::run(baseUrl: 'https://bad.test');

    expect($checks->firstWhere('message', 'Crawler endpoint /robots.txt returned HTTP 302')->detail)->toContain('redirect to https://bad.test/robots')
        ->and($checks->firstWhere('message', 'Crawler endpoint /llms.txt cache header')->status)->toBe('warn')
        ->and($checks->firstWhere('message', 'Generated output /llms.txt public leak scan')->detail)->toContain('/admin')
        ->and($checks->firstWhere('message', 'Sitemap endpoint /sitemap-xml XML validity')->status)->toBe('warn')
        ->and($checks->firstWhere('message', 'Sitemap endpoint /sitemap.xml unsafe URL scan')->status)->toBe('warn');
});

it('reports excluded ai discovery pages without reasons', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->language($language)->withTranslations($language)->create();
    $page = Page::factory()->site($site)->withTranslations($language)->create();

    AiDiscoveryPageProfile::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'include_in_ai_index' => false,
        'exclude_reason' => null,
        'section' => 'Pages',
        'priority' => 500,
    ]);

    $checks = BuildSeoSuiteDoctorReportAction::run(includeHttp: false);

    expect($checks->firstWhere('message', 'Excluded pages missing reasons')->status)->toBe('warn')
        ->and($checks->firstWhere('message', 'Excluded pages missing reasons')->detail)->toBe('1');
});

it('registers the seo suite doctor artisan command', function (): void {
    $this->artisan('capell:seo-suite-doctor', ['--skip-http' => true])
        ->assertSuccessful();
});
