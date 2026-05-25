<?php

declare(strict_types=1);

use Capell\CampaignStudio\Models\CampaignCtaBlock;
use Capell\CampaignStudio\View\Components\Block\CampaignCtaBlock as CampaignCtaBlockComponent;
use Capell\LayoutBuilder\Models\Widget;

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
