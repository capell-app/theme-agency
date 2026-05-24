<?php

declare(strict_types=1);

use Capell\Core\Enums\AssetEnum;
use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\FilamentPeek\Actions\RegisterLayoutBuilderPreviewBlocksAction;
use Capell\FilamentPeek\Data\LayoutBuilderPreviewStateData;
use Capell\LayoutBuilder\Models\Block;
use Capell\LayoutBuilder\Models\BlockAsset;
use Capell\LayoutBuilder\Support\CapellLayoutManager;

afterEach(function (): void {
    CapellLayoutManager::clearContainerBlocks();
});

it('registers preview layout blocks with unsaved widget asset state for public rendering', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->withTranslations($language)->language($language)->create();
    $layout = Layout::factory()->site($site)->create();
    $page = Page::factory()->site($site)->layout($layout)->withTranslations($language)->create();
    $linkedPage = Page::factory()->site($site)->layout($layout)->withTranslations($language)->create();
    $block = Block::factory()->create(['key' => 'asset-rail']);
    $savedAsset = BlockAsset::factory()
        ->block($block)
        ->page($page, 'main', 1)
        ->asset($linkedPage)
        ->create([
            'meta' => ['caption' => 'Saved caption'],
            'order' => 1,
        ]);

    $state = new LayoutBuilderPreviewStateData(
        layoutId: (int) $layout->getKey(),
        containers: [
            'main' => [
                'blocks' => [
                    ['block_key' => $block->key, 'occurrence' => 1],
                ],
            ],
        ],
        assets: [
            'main' => [
                [
                    [
                        'id' => $savedAsset->getKey(),
                        'block_id' => $block->getKey(),
                        'asset_type' => AssetEnum::Page->value,
                        'asset_id' => $linkedPage->getKey(),
                        'container' => 'main',
                        'pageable_type' => $page->getMorphClass(),
                        'pageable_id' => $page->getKey(),
                        'occurrence' => 1,
                        'order' => 1,
                        'meta' => ['caption' => 'Unsaved caption'],
                    ],
                ],
            ],
        ],
    );

    $registered = RegisterLayoutBuilderPreviewBlocksAction::run($page, $language, $state);
    $previewBlock = CapellLayoutManager::getStoredContainerBlock('main', 'asset-rail', 1);
    $previewAsset = $previewBlock?->assets->first();

    expect($registered)->toBeTrue()
        ->and($previewBlock)->toBeInstanceOf(Block::class)
        ->and($previewAsset)->toBeInstanceOf(BlockAsset::class)
        ->and($previewAsset->meta)->toBe(['caption' => 'Unsaved caption'])
        ->and($previewAsset->asset)->toBeInstanceOf(Page::class)
        ->and($previewAsset->asset->is($linkedPage))->toBeTrue()
        ->and($savedAsset->fresh()->meta)->toBe(['caption' => 'Saved caption']);
});
