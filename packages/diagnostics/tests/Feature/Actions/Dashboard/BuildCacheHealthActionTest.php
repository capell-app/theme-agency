<?php

declare(strict_types=1);

use Capell\Core\Enums\UrlTypeEnum;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Diagnostics\Actions\Dashboard\BuildCacheHealthAction;
use Capell\HtmlCache\Models\CachedModelUrl;
use Capell\HtmlCache\Providers\HtmlCacheServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    app()->register(HtmlCacheServiceProvider::class);

    Schema::dropIfExists('cached_model_urls');
    Schema::create('cached_model_urls', function (Blueprint $table): void {
        $table->id();
        $table->string('url', 2048);
        $table->char('url_hash', 64);
        $table->string('path', 2048);
        $table->foreignId('site_id')->nullable();
        $table->foreignId('site_domain_id')->nullable();
        $table->foreignId('language_id')->nullable();
        $table->morphs('cacheable');
        $table->timestamp('cached_at')->nullable();
        $table->timestamp('last_seen_at')->nullable();
        $table->timestamps();
    });
});

it('summarizes cache coverage for enabled non-redirect page URLs on a site', function (): void {
    config(['capell-html-cache.enabled' => true]);

    $currentTime = now()->toImmutable();
    $lastWarmedAt = $currentTime->subHour();

    $language = Language::factory()->create();
    $site = Site::factory()
        ->language($language)
        ->withTranslations($language, siteDomainData: [
            'domain' => 'cache-health.test',
            'scheme' => 'https',
            'path' => null,
            'default' => true,
        ])
        ->create(['name' => 'Cache Health']);
    $otherSite = Site::factory()->language($language)->withTranslations($language)->create();
    $siteDomain = $site->siteDomains()->where('language_id', $language->getKey())->firstOrFail();

    $cachedPage = Page::factory()->site($site)->withTranslations($language)->create();
    $stalePage = Page::factory()->site($site)->withTranslations($language)->create();
    $missingPage = Page::factory()->site($site)->withTranslations($language)->create();
    $redirectPage = Page::factory()->site($site)->withTranslations($language)->create();
    $disabledPage = Page::factory()->site($site)->withTranslations($language)->create();
    $otherSitePage = Page::factory()->site($otherSite)->withTranslations($language)->create();

    PageUrl::query()->delete();

    $cachedUrl = PageUrl::factory()->page($cachedPage)->site($site)->language($language)->state([
        'url' => '/cached',
        'status' => true,
        'updated_at' => $currentTime->subDays(2),
    ])->create();
    $staleUrl = PageUrl::factory()->page($stalePage)->site($site)->language($language)->state([
        'url' => '/stale',
        'status' => true,
        'updated_at' => $currentTime,
    ])->create();
    PageUrl::factory()->page($missingPage)->site($site)->language($language)->state([
        'url' => '/missing',
        'status' => true,
    ])->create();
    PageUrl::factory()->page($redirectPage)->site($site)->language($language)->state([
        'url' => '/redirect',
        'type' => UrlTypeEnum::Redirect,
        'status' => true,
    ])->create();
    PageUrl::factory()->page($disabledPage)->site($site)->language($language)->state([
        'url' => '/disabled',
        'status' => false,
    ])->create();
    PageUrl::factory()->page($otherSitePage)->site($otherSite)->language($language)->state([
        'url' => '/other-site',
        'status' => true,
    ])->create();

    CachedModelUrl::query()->create([
        'url' => 'https://cache-health.test/cached',
        'url_hash' => CachedModelUrl::hashUrl('https://cache-health.test/cached'),
        'path' => '/cached',
        'site_id' => $site->getKey(),
        'site_domain_id' => $siteDomain->getKey(),
        'language_id' => $language->getKey(),
        'cacheable_type' => $cachedUrl->getMorphClass(),
        'cacheable_id' => $cachedUrl->getKey(),
        'cached_at' => $lastWarmedAt,
        'last_seen_at' => $lastWarmedAt,
    ]);
    CachedModelUrl::query()->create([
        'url' => 'https://cache-health.test/stale',
        'url_hash' => CachedModelUrl::hashUrl('https://cache-health.test/stale'),
        'path' => '/stale',
        'site_id' => $site->getKey(),
        'site_domain_id' => $siteDomain->getKey(),
        'language_id' => $language->getKey(),
        'cacheable_type' => $staleUrl->getMorphClass(),
        'cacheable_id' => $staleUrl->getKey(),
        'cached_at' => now()->subDays(3),
        'last_seen_at' => now()->subDays(3),
    ]);

    $data = BuildCacheHealthAction::run($site);

    expect($data->siteId)->toBe($site->getKey())
        ->and($data->siteName)->toBe('Cache Health')
        ->and($data->totalEnabledUrls)->toBe(3)
        ->and($data->cachedCount)->toBe(1)
        ->and($data->staleCount)->toBe(1)
        ->and($data->missingCount)->toBe(1)
        ->and($data->lastWarmedAt)->toBe($lastWarmedAt->toIso8601String())
        ->and(collect($data->eligibilityReports)->pluck('url')->all())
        ->toContain(
            'https://cache-health.test/cached',
            'https://cache-health.test/stale',
            'https://cache-health.test/missing',
        )
        ->not->toContain('https://cache-health.test/redirect', 'https://cache-health.test/disabled');
});
