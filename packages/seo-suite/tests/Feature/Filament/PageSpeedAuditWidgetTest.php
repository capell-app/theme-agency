<?php

declare(strict_types=1);

use Capell\Admin\Filament\Resources\Pages\Tables\PagesTable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Contracts\PageSpeedInsightsClientInterface;
use Capell\SeoSuite\Data\PageSpeedAuditResultData;
use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Capell\SeoSuite\Filament\Extenders\PageSpeed\PageSpeedPageTableExtender;
use Capell\SeoSuite\Filament\Widgets\EditPagePageSpeedAuditWidget;
use Capell\SeoSuite\Jobs\RunPageSpeedAuditJob;
use Capell\SeoSuite\Models\PageSpeedAuditResult;
use Capell\SeoSuite\Models\PageSpeedAuditRun;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

uses(CreatesAdminUser::class);

beforeEach(function (): void {
    test()->actingAsAdmin();
});

it('contributes a PageSpeed column and filter to the page table extender', function (): void {
    $extender = resolve(PageSpeedPageTableExtender::class);

    expect($extender->getColumns())->toHaveCount(1)
        ->and($extender->getFilters())->toHaveCount(1)
        ->and($extender->getBulkActions())->toBe([]);
});

it('adds the PageSpeed filter to the CMS pages table', function (): void {
    $method = new ReflectionMethod(PagesTable::class, 'getTableFilters');

    /** @var array<int, object> $filters */
    $filters = $method->invoke(null);

    $filterNames = collect($filters)
        ->map(static fn (object $filter): string => method_exists($filter, 'getName') ? (string) $filter->getName() : '')
        ->all();

    expect($filterNames)->toContain('page_speed_status');
});

it('filters pages by the latest PageSpeed result per strategy', function (): void {
    [$site, $language, $page] = createPageSpeedWidgetPage('/filtered');
    $runId = createPageSpeedAuditRunId();

    PageSpeedAuditResult::query()->create([
        'page_speed_audit_run_id' => $runId,
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'url' => 'https://example.test/filtered',
        'strategy' => PageSpeedStrategyEnum::Mobile->value,
        'status' => 'succeeded',
        'performance_score' => 34,
        'fetched_at' => now()->subDays(2),
    ]);

    PageSpeedAuditResult::query()->create([
        'page_speed_audit_run_id' => $runId,
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'url' => 'https://example.test/filtered',
        'strategy' => PageSpeedStrategyEnum::Mobile->value,
        'status' => 'succeeded',
        'performance_score' => 96,
        'fetched_at' => now(),
    ]);

    $method = new ReflectionMethod(PageSpeedPageTableExtender::class, 'applyStatusFilter');

    $query = $method->invoke(resolve(PageSpeedPageTableExtender::class), Page::query(), ['value' => 'poor']);

    expect($query->pluck('id')->all())->not->toContain($page->getKey());
});

it('renders page speed results on the edit page widget', function (): void {
    [$site, $language, $page] = createPageSpeedWidgetPage('/about');

    PageSpeedAuditResult::query()->create([
        'page_speed_audit_run_id' => createPageSpeedAuditRunId(),
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'url' => 'https://example.test/about',
        'strategy' => PageSpeedStrategyEnum::Mobile->value,
        'status' => 'succeeded',
        'performance_score' => 42,
        'accessibility_score' => 90,
        'best_practices_score' => 88,
        'seo_score' => 100,
        'metrics' => ['largest-contentful-paint' => ['display_value' => '3.2 s', 'numeric_value' => 3200]],
        'opportunities' => [['key' => 'uses-webp-images', 'title' => 'Serve images in next-gen formats']],
        'diagnostics' => [['key' => 'dom-size', 'title' => 'Avoid an excessive DOM size']],
        'fetched_at' => now(),
    ]);

    Livewire::test(EditPagePageSpeedAuditWidget::class, ['record' => $page])
        ->assertSeeText(__('capell-seo-suite::generic.pagespeed_audit'))
        ->assertSeeText('42')
        ->assertSeeText('Serve images in next-gen formats');
});

it('queues a manual page speed audit from the widget', function (): void {
    Queue::fake();

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
                categoryScores: ['performance' => 91, 'accessibility' => 90, 'best-practices' => 88, 'seo' => 100],
            );
        }
    });

    [, , $page] = createPageSpeedWidgetPage('/manual');

    Livewire::test(EditPagePageSpeedAuditWidget::class, ['record' => $page])
        ->call('runAudit')
        ->assertHasNoErrors();

    Queue::assertPushed(RunPageSpeedAuditJob::class);
    expect(PageSpeedAuditResult::query()->where('page_id', $page->getKey())->count())->toBe(0);
});

/**
 * @return array{Site, Language, Page}
 */
function createPageSpeedWidgetPage(string $url): array
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

function createPageSpeedAuditRunId(): int
{
    return PageSpeedAuditRun::query()->create([
        'trigger' => 'manual',
        'status' => 'succeeded',
        'started_at' => now(),
        'completed_at' => now(),
    ])->getKey();
}
