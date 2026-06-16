<?php

declare(strict_types=1);

use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Capell\CampaignStudio\Providers\CampaignStudioServiceProvider;
use Capell\CampaignStudio\Support\PublicUrls\CampaignLandingPagePublicUrlContributor;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\SiteDiscovery\Contracts\PublicUrlContributor;
use Capell\SiteDiscovery\Data\PublicUrlData;

it('contributes campaign landing page URLs to the public URL registry contract', function (): void {
    $campaignGroup = CampaignGroup::factory()->create();
    $page = Page::factory()->create([
        'meta' => [
            'priority' => 0.6,
            'changefreq' => 'weekly',
        ],
    ]);

    $pageUrl = PageUrl::factory()
        ->page($page)
        ->create([
            'site_id' => $page->site_id,
            'url' => '/campaign/spring',
        ]);

    CampaignLandingPage::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create(['page_id' => $page->getKey()]);

    $urls = (new CampaignLandingPagePublicUrlContributor)->publicUrls();

    expect($urls->first())->toBeInstanceOf(PublicUrlData::class)
        ->and($urls->pluck('canonicalUrl')->all())->toContain($pageUrl->full_url)
        ->and($urls->first()?->sourcePackage)->toBe(CampaignStudioServiceProvider::$packageName)
        ->and($urls->first()?->priority)->toBe('0.6')
        ->and($urls->first()?->changeFrequency)->toBe('weekly')
        ->and($urls->first()?->title)->toBe($page->translation->label ?? $page->name);
});

it('deduplicates campaign landing page URLs while scanning landing pages in chunks', function (): void {
    $firstCampaignGroup = CampaignGroup::factory()->create();
    $secondCampaignGroup = CampaignGroup::factory()->create();
    $page = Page::factory()->create();

    $pageUrl = PageUrl::factory()
        ->page($page)
        ->create([
            'site_id' => $page->site_id,
            'url' => '/campaign/shared',
        ]);

    CampaignLandingPage::factory()
        ->for($firstCampaignGroup, 'campaignGroup')
        ->create(['page_id' => $page->getKey()]);

    CampaignLandingPage::factory()
        ->for($secondCampaignGroup, 'campaignGroup')
        ->create(['page_id' => $page->getKey()]);

    $urls = (new CampaignLandingPagePublicUrlContributor)->publicUrls();

    expect($urls->pluck('canonicalUrl')->all())
        ->toBe([$pageUrl->full_url]);
});

it('registers the campaign landing page public URL contributor when Site Discovery is available', function (): void {
    $contributors = collect(app()->tagged(PublicUrlContributor::TAG));

    expect($contributors->contains(fn (mixed $contributor): bool => $contributor instanceof CampaignLandingPagePublicUrlContributor))->toBeTrue();
});
