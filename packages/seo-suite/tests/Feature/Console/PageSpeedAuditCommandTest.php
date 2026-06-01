<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Contracts\PageSpeedInsightsClientInterface;
use Capell\SeoSuite\Data\PageSpeedAuditResultData;
use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Capell\SeoSuite\Models\PageSpeedAuditResult;
use Capell\SeoSuite\Support\PageSpeed\NullPageSpeedInsightsClient;
use Illuminate\Console\Command;

it('runs a PageSpeed audit from the console command with strategy and limit options', function (): void {
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
                categoryScores: ['performance' => 80],
            );
        }
    });

    createPageSpeedCommandPage('/one');
    createPageSpeedCommandPage('/two');

    $this->artisan('capell:seo-suite:pagespeed-audit', [
        '--strategy' => 'mobile',
        '--limit' => 1,
    ])->assertSuccessful();

    expect(PageSpeedAuditResult::query()->count())->toBe(1)
        ->and(PageSpeedAuditResult::query()->first()?->strategy)->toBe(PageSpeedStrategyEnum::Mobile);
});

it('rejects invalid PageSpeed command strategy and integer options', function (): void {
    $this->artisan('capell:seo-suite:pagespeed-audit', [
        '--strategy' => 'tablet',
    ])->assertExitCode(Command::FAILURE);

    $this->artisan('capell:seo-suite:pagespeed-audit', [
        '--limit' => 'many',
    ])->assertExitCode(Command::FAILURE);
});

it('returns an explicit failed audit result when PageSpeed is not configured', function (): void {
    $client = new NullPageSpeedInsightsClient;

    $result = $client->analyze('https://example.test', PageSpeedStrategyEnum::Desktop);

    expect($client->isConfigured())->toBeFalse()
        ->and($result->successful)->toBeFalse()
        ->and($result->url)->toBe('https://example.test')
        ->and($result->strategy)->toBe(PageSpeedStrategyEnum::Desktop)
        ->and($result->errorMessage)->toBe(__('capell-seo-suite::generic.pagespeed_not_configured'));
});

function createPageSpeedCommandPage(string $url): void
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

        return;
    }

    PageUrl::factory()
        ->page($page)
        ->site($site)
        ->language($language)
        ->create(['url' => $url]);
}
