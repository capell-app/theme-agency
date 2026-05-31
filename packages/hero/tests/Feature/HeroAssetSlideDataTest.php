<?php

declare(strict_types=1);

use Capell\Core\Enums\MediaCollectionEnum;
use Capell\Core\Models\Media;
use Capell\Core\Models\Page;
use Capell\Hero\Data\HeroAssetSlideData;
use Capell\Hero\Health\HeroHealthCheck;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Models\WidgetAsset;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;

it('builds hero asset slide data from a linked page asset', function (): void {
    $page = Page::factory()->create(['meta' => ['color' => 'brand']]);
    $block = new Widget;
    $blockAsset = new WidgetAsset;
    $backgroundImage = new Media;
    $backgroundImage->collection_name = MediaCollectionEnum::BackgroundImage->value;

    $image = new Media;
    $image->collection_name = MediaCollectionEnum::Image->value;

    $page->setRelation('pageUrl', null);
    $blockAsset->setRelation('asset', $page);
    $blockAsset->setRelation('media', new EloquentCollection([$backgroundImage, $image]));

    $data = HeroAssetSlideData::fromBlockAsset($blockAsset, $block, 'fallback');

    expect($data->asset)->toBe($page)
        ->and($data->color)->toBe('brand')
        ->and($data->linkedPage)->toBe($page)
        ->and($data->backgroundImage)->toBe($backgroundImage)
        ->and($data->images?->all())->toBe([$image])
        ->and(HeroHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('uses media assets directly as the hero background image', function (): void {
    $block = new Widget;
    $blockAsset = new WidgetAsset;
    $media = new Media;
    $media->collection_name = MediaCollectionEnum::Image->value;

    $blockAsset->setRelation('asset', $media);

    $data = HeroAssetSlideData::fromBlockAsset($blockAsset, $block, 'fallback');

    expect($data->asset)->toBe($media)
        ->and($data->color)->toBe('fallback')
        ->and($data->linkedPage)->toBeNull()
        ->and($data->backgroundImage)->toBe($media)
        ->and($data->images)->toBeNull();
});

it('does not lazy load optional public hero relations', function (): void {
    $previousLazyLoadingState = Model::preventsLazyLoading();
    Model::preventLazyLoading();

    try {
        $page = Page::factory()->create(['meta' => ['color' => 'brand']]);
        $block = new Widget;
        $blockAsset = new WidgetAsset;
        $blockAsset->setRelation('asset', $page);

        $data = HeroAssetSlideData::fromBlockAsset($blockAsset, $block, 'fallback');
    } finally {
        Model::preventLazyLoading($previousLazyLoadingState);
    }

    expect($data->linkedPage)->toBe($page)
        ->and($data->url)->toBeNull()
        ->and($data->backgroundImage)->toBeNull()
        ->and($data->images)->toBeNull();
});

it('keeps public hero blade on prepared slide data', function (): void {
    $hero = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/block/hero.blade.php');
    $related = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/hero/related.blade.php');

    expect($hero)
        ->not->toContain('$slide->asset->translation')
        ->not->toContain('$slide->asset->related')
        ->not->toContain('$slide->asset->getMeta')
        ->not->toContain('$page->translation')
        ->not->toContain('$block->assets')
        ->not->toContain('$block->translation')
        ->not->toContain('$block->getMeta')
        ->not->toContain('$blockAsset')
        ->not->toContain('Frontend::')
        ->not->toContain('GetPageVariablesAction')
        ->not->toContain('RenderHtmlContentAction')
        ->not->toContain('ResolveHeroBackgroundDataAction')
        ->not->toContain('ResolveHeroMediaDataAction')
        ->not->toContain('HeroAssetSlideData::')
        ->and($related)
        ->not->toContain('Frontend::')
        ->not->toContain('getRelations')
        ->not->toContain('getMeta')
        ->not->toContain('$feature->')
        ->not->toContain('$feature->linkedPage')
        ->not->toContain('$feature->pageUrl')
        ->not->toContain('$feature->image')
        ->not->toContain('$feature->translation');
});
