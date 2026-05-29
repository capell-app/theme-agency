<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Actions\ResolvePageSpeedAuditTargetsAction;
use Capell\SeoSuite\Actions\RunPageSpeedAuditAction;
use Capell\SeoSuite\Contracts\PageSpeedInsightsClientInterface;
use Capell\SeoSuite\Data\PageSpeedAuditItemData;
use Capell\SeoSuite\Data\PageSpeedAuditResultData;
use Capell\SeoSuite\Enums\PageSpeedAuditRunStatusEnum;
use Capell\SeoSuite\Enums\PageSpeedAuditTriggerEnum;
use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Capell\SeoSuite\Models\PageSpeedAuditResult;
use Capell\SeoSuite\Models\PageSpeedAuditRun;

it('audits published public page urls and persists mobile and desktop results', function (): void {
    app()->instance(PageSpeedInsightsClientInterface::class, new class implements PageSpeedInsightsClientInterface
    {
        public function isConfigured(): bool
        {
            return true;
        }

        public function analyze(string $url, PageSpeedStrategyEnum $strategy): PageSpeedAuditResultData
        {
            return new PageSpeedAuditResultData(
                strategy: $strategy,
                url: $url,
                successful: true,
                categoryScores: [
                    'performance' => $strategy === PageSpeedStrategyEnum::Mobile ? 44 : 92,
                    'accessibility' => 90,
                    'best-practices' => 88,
                    'seo' => 100,
                ],
                metrics: ['largest-contentful-paint' => ['display_value' => '2.1 s', 'numeric_value' => 2100]],
                opportunities: [new PageSpeedAuditItemData('uses-webp-images', 'Serve images in next-gen formats')],
                diagnostics: [new PageSpeedAuditItemData('dom-size', 'Avoid an excessive DOM size')],
            );
        }
    });

    [$site, $language, $page] = createPageSpeedAuditPage('/about');
    $pendingPage = Page::factory()
        ->site($site)
        ->withTranslations($language)
        ->create(['visible_from' => now()->addDay()]);
    PageUrl::factory()->page($pendingPage)->site($site)->language($language)->create(['url' => '/draft']);

    $summary = RunPageSpeedAuditAction::run(
        trigger: PageSpeedAuditTriggerEnum::Manual,
        strategies: PageSpeedStrategyEnum::cases(),
    );

    expect($summary->auditedPages)->toBe(1)
        ->and($summary->successfulResults)->toBe(2)
        ->and($summary->failedResults)->toBe(0)
        ->and(PageSpeedAuditRun::query()->first()?->status)->toBe(PageSpeedAuditRunStatusEnum::Succeeded)
        ->and(PageSpeedAuditResult::query()->count())->toBe(2)
        ->and(PageSpeedAuditResult::query()->where('page_id', $page->getKey())->where('strategy', 'mobile')->first()?->performance_score)->toBe(44)
        ->and(PageSpeedAuditResult::query()->where('page_id', $pendingPage->getKey())->exists())->toBeFalse();
});

it('records failed PageSpeed results without failing the whole run', function (): void {
    app()->instance(PageSpeedInsightsClientInterface::class, new class implements PageSpeedInsightsClientInterface
    {
        public function isConfigured(): bool
        {
            return true;
        }

        public function analyze(string $url, PageSpeedStrategyEnum $strategy): PageSpeedAuditResultData
        {
            return new PageSpeedAuditResultData(
                strategy: $strategy,
                url: $url,
                successful: false,
                errorMessage: 'Page stopped responding.',
            );
        }
    });

    createPageSpeedAuditPage('/slow');

    $summary = RunPageSpeedAuditAction::run(
        trigger: PageSpeedAuditTriggerEnum::Command,
        strategies: [PageSpeedStrategyEnum::Mobile],
    );

    $result = PageSpeedAuditResult::query()->first();

    expect($summary->failedResults)->toBe(1)
        ->and($summary->run->status)->toBe(PageSpeedAuditRunStatusEnum::SucceededWithErrors)
        ->and($result?->status)->toBe('failed')
        ->and($result?->error_message)->toBe('Page stopped responding.');
});

it('prefers generated public page urls when resolving one PageSpeed target per language', function (): void {
    [$site, $language, $page] = createPageSpeedAuditPage('/about');

    PageUrl::factory()
        ->page($page)
        ->site($site)
        ->language($language)
        ->create([
            'url' => '/manual-campaign',
            'is_manual' => true,
            'type' => null,
        ]);

    $targets = ResolvePageSpeedAuditTargetsAction::run(pageId: (int) $page->getKey());

    expect($targets)->toHaveCount(1)
        ->and($targets[0]->url)->toBe('https://example.test/about');
});

it('summarizes worst scores, low scores, and score drops for digests', function (): void {
    app()->instance(PageSpeedInsightsClientInterface::class, new class implements PageSpeedInsightsClientInterface
    {
        public function isConfigured(): bool
        {
            return true;
        }

        public function analyze(string $url, PageSpeedStrategyEnum $strategy): PageSpeedAuditResultData
        {
            return new PageSpeedAuditResultData(
                strategy: $strategy,
                url: $url,
                successful: true,
                categoryScores: ['performance' => 42],
            );
        }
    });

    [$site, $language, $page] = createPageSpeedAuditPage('/dropping');
    $previousRun = PageSpeedAuditRun::query()->create([
        'trigger' => 'manual',
        'status' => 'succeeded',
        'started_at' => now()->subDays(7),
        'completed_at' => now()->subDays(7),
    ]);

    PageSpeedAuditResult::query()->create([
        'page_speed_audit_run_id' => $previousRun->getKey(),
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'url' => 'https://example.test/dropping',
        'strategy' => PageSpeedStrategyEnum::Mobile->value,
        'status' => 'succeeded',
        'performance_score' => 90,
        'fetched_at' => now()->subDays(7),
    ]);

    $summary = RunPageSpeedAuditAction::run(
        trigger: PageSpeedAuditTriggerEnum::Command,
        strategies: [PageSpeedStrategyEnum::Mobile],
    );

    expect($summary->worstMobileResults[0]->score)->toBe(42)
        ->and($summary->belowThresholdResults[0]->score)->toBe(42)
        ->and($summary->biggestDrops[0]->previousScore)->toBe(90)
        ->and($summary->biggestDrops[0]->drop)->toBe(48);
});

/**
 * @return array{Site, Language, Page}
 */
function createPageSpeedAuditPage(string $url): array
{
    $language = Language::factory()->create();
    $site = Site::factory()
        ->language($language)
        ->withTranslations($language, siteDomainData: ['scheme' => 'https', 'domain' => 'example.test', 'path' => null])
        ->create();
    $page = Page::factory()
        ->site($site)
        ->withTranslations($language)
        ->create();

    $pageUrl = $page->pageUrls()->first();

    if ($pageUrl instanceof PageUrl) {
        $pageUrl->update([
            'site_id' => $site->getKey(),
            'language_id' => $language->getKey(),
            'url' => $url,
            'status' => true,
            'type' => null,
        ]);
    } else {
        PageUrl::factory()
            ->page($page)
            ->site($site)
            ->language($language)
            ->create(['url' => $url]);
    }

    return [$site, $language, $page];
}
