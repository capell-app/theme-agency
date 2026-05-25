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
