<?php

declare(strict_types=1);

use Capell\CampaignStudio\Models\CampaignCtaBlock;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\View\Components\Block\CampaignCtaBlock as CampaignCtaBlockComponent;
use Capell\Core\Models\Site;
use Capell\Frontend\Support\State\FrontendState;
use Capell\LayoutBuilder\Models\Widget;
use Illuminate\Contracts\View\View;

it('hydrates CTA blocks for campaign blocks in one batch', function (): void {
    $firstCtaBlock = CampaignCtaBlock::factory()->create(['headline' => 'First CTA']);
    $secondCtaBlock = CampaignCtaBlock::factory()->create(['headline' => 'Second CTA']);
    $firstBlock = Widget::factory()->create(['meta' => ['cta_block_id' => $firstCtaBlock->getKey()]]);
    $secondBlock = Widget::factory()->create(['meta' => ['cta_block_id' => $secondCtaBlock->getKey()]]);

    CampaignCtaBlockComponent::hydrateBlocks(collect([$firstBlock, $secondBlock]));

    expect($firstBlock->relationLoaded('campaignCtaBlock'))->toBeTrue()
        ->and($firstBlock->getRelation('campaignCtaBlock')->headline)->toBe('First CTA')
        ->and($secondBlock->relationLoaded('campaignCtaBlock'))->toBeTrue()
        ->and($secondBlock->getRelation('campaignCtaBlock')->headline)->toBe('Second CTA');
});

it('hydrates only active CTA blocks for the current frontend site', function (): void {
    $site = Site::factory()->create();
    $otherSite = Site::factory()->create();
    resolve(FrontendState::class)->withSite($site);

    $siteCtaBlock = CampaignCtaBlock::factory()->create([
        'site_id' => $site->getKey(),
        'headline' => 'Site CTA',
    ]);
    $hiddenCtaBlock = CampaignCtaBlock::factory()->create([
        'site_id' => $otherSite->getKey(),
        'headline' => 'Hidden CTA',
    ]);
    $inactiveCtaBlock = CampaignCtaBlock::factory()->create([
        'site_id' => $site->getKey(),
        'is_active' => false,
        'headline' => 'Inactive CTA',
    ]);
    $siteBlock = Widget::factory()->create(['meta' => ['cta_block_id' => $siteCtaBlock->getKey()]]);
    $hiddenBlock = Widget::factory()->create(['meta' => ['cta_block_id' => $hiddenCtaBlock->getKey()]]);
    $inactiveBlock = Widget::factory()->create(['meta' => ['cta_block_id' => $inactiveCtaBlock->getKey()]]);

    CampaignCtaBlockComponent::hydrateBlocks(collect([$siteBlock, $hiddenBlock, $inactiveBlock]));

    expect($siteBlock->getRelation('campaignCtaBlock')?->headline)->toBe('Site CTA')
        ->and($hiddenBlock->getRelation('campaignCtaBlock'))->toBeNull()
        ->and($inactiveBlock->getRelation('campaignCtaBlock'))->toBeNull();
});

it('renders public CTA tracking attributes without leaking numeric campaign ids', function (): void {
    $campaignGroup = CampaignGroup::factory()->create(['slug' => 'spring-launch']);
    $ctaBlock = CampaignCtaBlock::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create([
            'key' => 'primary-cta',
            'headline' => 'Start today',
        ]);
    $block = Widget::factory()->create(['meta' => ['cta_block_id' => $ctaBlock->getKey()]]);

    CampaignCtaBlockComponent::hydrateBlocks(collect([$block]));

    $component = new CampaignCtaBlockComponent(
        container: [],
        containerKey: 'main',
        blockIndex: 0,
        loop: (object) ['index' => 0, 'first' => true, 'last' => true],
        block: $block,
    );

    $rendered = $component->render([
        'container' => [],
        'containerKey' => 'main',
        'containerWidth' => null,
        'loop' => (object) ['index' => 0, 'first' => true, 'last' => true],
        'block' => $block,
    ]);
    $html = match (true) {
        is_string($rendered) => $rendered,
        $rendered instanceof View => $rendered->render(),
        default => throw new RuntimeException('Campaign CTA block did not return renderable output.'),
    };

    expect($html)
        ->toContain('data-campaign="spring-launch"')
        ->toContain('data-campaign-cta="primary-cta"')
        ->not->toContain('data-campaign-id')
        ->not->toContain('campaign_group_id');
});
