<?php

declare(strict_types=1);

use Capell\Core\Models\Media;
use Capell\Core\Models\Theme;
use Capell\Hero\Actions\ResolveHeroBackgroundDataAction;
use Capell\Hero\Actions\ResolveHeroMediaDataAction;
use Capell\Hero\Data\HeroBackgroundData;
use Capell\Hero\Data\HeroMediaData;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Models\WidgetAsset;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

it('resolves hero background from theme widget and asset layers', function (): void {
    $theme = Theme::factory()->create([
        'meta' => [
            'hero_background' => [
                'mode' => 'custom',
                'background_color' => '#eaf2ff',
                'accent_color' => '#245f8f',
                'accent_color_alt' => '#8db9dc',
                'overlay_style' => 'grid',
                'overlay_opacity' => '0.44',
            ],
        ],
    ]);
    $widget = Widget::factory()->create([
        'meta' => [
            'hero_background' => [
                'mode' => 'custom',
                'background_color' => '#f8f3e8',
                'overlay_style' => 'contours',
            ],
        ],
    ]);
    $asset = WidgetAsset::factory()->create([
        'meta' => [
            'hero_background' => [
                'mode' => 'custom',
                'accent_color' => '#7251a3',
            ],
        ],
    ]);

    $background = ResolveHeroBackgroundDataAction::run($theme, $widget, $asset);

    expect($background->enabled)->toBeTrue()
        ->and($background->backgroundColor)->toBe('#f8f3e8')
        ->and($background->accentColor)->toBe('#7251a3')
        ->and($background->accentColorAlt)->toBe('#8db9dc')
        ->and($background->overlayStyle)->toBe('contours')
        ->and($background->overlayOpacity)->toBe(0.44);
});

it('allows widget and asset layers to turn the hero background off', function (): void {
    $theme = Theme::factory()->create([
        'meta' => [
            'hero_background' => [
                'mode' => 'custom',
                'background_color' => '#eaf2ff',
            ],
        ],
    ]);
    $widget = Widget::factory()->create([
        'meta' => [
            'hero_background' => [
                'mode' => 'inherit',
            ],
        ],
    ]);
    $asset = WidgetAsset::factory()->create([
        'meta' => [
            'hero_background' => [
                'mode' => 'off',
            ],
        ],
    ]);

    expect(ResolveHeroBackgroundDataAction::run($theme, $widget, $asset)->enabled)->toBeFalse();
});

it('ignores invalid hero background values and clamps opacity', function (): void {
    $theme = Theme::factory()->create([
        'meta' => [
            'hero_background' => [
                'mode' => 'custom',
                'background_color' => 'not-a-color',
                'accent_color' => '#ABC',
                'accent_color_alt' => '#123456',
                'overlay_style' => 'unknown',
                'overlay_opacity' => '2.8',
            ],
        ],
    ]);

    $background = ResolveHeroBackgroundDataAction::run($theme);

    expect($background->backgroundColor)->toBe(HeroBackgroundData::defaults()->backgroundColor)
        ->and($background->accentColor)->toBe('#abc')
        ->and($background->accentColorAlt)->toBe('#123456')
        ->and($background->overlayStyle)->toBe(HeroBackgroundData::defaults()->overlayStyle)
        ->and($background->overlayOpacity)->toBe(1.0)
        ->and($background->cssVariables())->toMatchArray([
            '--hero-background-color' => HeroBackgroundData::defaults()->backgroundColor,
            '--hero-overlay-opacity' => '1',
            '--hero-accent-color' => '#abc',
            '--hero-accent-color-alt' => '#123456',
        ]);
});

it('resolves responsive hero media from theme widget and asset layers', function (): void {
    $theme = Theme::factory()->create([
        'meta' => [
            'hero_media' => [
                'mode' => 'custom',
                'autoplay' => true,
                'loop' => true,
                'muted' => true,
                'pause_when_out_of_view' => true,
                'preload' => 'metadata',
            ],
        ],
    ]);
    $widget = Widget::factory()->create([
        'meta' => [
            'hero_media' => [
                'mode' => 'custom',
                'loop' => false,
                'preload' => 'none',
            ],
        ],
    ]);
    $asset = WidgetAsset::factory()->create([
        'meta' => [
            'hero_media' => [
                'mode' => 'custom',
                'autoplay' => false,
            ],
        ],
    ]);

    $themeDesktopVideo = mediaFor($theme, HeroMediaData::CollectionDesktopVideo, 'theme-desktop.webm', 'video/webm');
    $widgetMobileVideo = mediaFor($widget, HeroMediaData::CollectionMobileVideo, 'widget-mobile.mp4', 'video/mp4');
    $assetDesktopImage = mediaFor($asset, HeroMediaData::CollectionDesktopImage, 'asset-desktop.jpg', 'image/jpeg');

    $theme->setRelation('media', new EloquentCollection([$themeDesktopVideo]));
    $widget->setRelation('media', new EloquentCollection([$widgetMobileVideo]));
    $asset->setRelation('media', new EloquentCollection([$assetDesktopImage]));

    $media = ResolveHeroMediaDataAction::run($theme, $widget, $asset);
    $desktopVideo = $media->videos['desktop'] ?? null;
    $mobileVideo = $media->videos['mobile'] ?? null;
    $desktopImage = $media->images['desktop'] ?? null;

    throw_if(! $desktopVideo instanceof Media || ! $mobileVideo instanceof Media || ! $desktopImage instanceof Media, RuntimeException::class, 'Expected responsive hero media assets to resolve.');

    expect($media->enabled)->toBeTrue()
        ->and($media->autoplay)->toBeFalse()
        ->and($media->loop)->toBeFalse()
        ->and($media->muted)->toBeTrue()
        ->and($media->pauseWhenOutOfView)->toBeTrue()
        ->and($media->preload)->toBe(HeroMediaData::PreloadNone)
        ->and($desktopVideo->is($themeDesktopVideo))->toBeTrue()
        ->and($mobileVideo->is($widgetMobileVideo))->toBeTrue()
        ->and($desktopImage->is($assetDesktopImage))->toBeTrue();
});

it('allows a hero media layer to disable inherited responsive media', function (): void {
    $theme = Theme::factory()->create([
        'meta' => [
            'hero_media' => ['mode' => 'custom'],
        ],
    ]);
    $widget = Widget::factory()->create([
        'meta' => [
            'hero_media' => ['mode' => 'off'],
        ],
    ]);

    $theme->setRelation('media', new EloquentCollection([
        mediaFor($theme, HeroMediaData::CollectionDesktopVideo, 'theme-desktop.webm', 'video/webm'),
    ]));

    expect(ResolveHeroMediaDataAction::run($theme, $widget)->enabled)->toBeFalse();
});

it('reports hero media presence and prefers desktop posters', function (): void {
    $desktopImage = new Media;
    $tabletImage = new Media;
    $desktopVideo = new Media;

    $media = new HeroMediaData(
        enabled: true,
        autoplay: true,
        loop: true,
        muted: true,
        pauseWhenOutOfView: true,
        preload: HeroMediaData::PreloadMetadata,
        videos: ['desktop' => $desktopVideo],
        images: ['tablet' => $tabletImage, 'desktop' => $desktopImage],
    );

    expect($media->hasVideo())->toBeTrue()
        ->and($media->hasImage())->toBeTrue()
        ->and($media->poster())->toBe($desktopImage)
        ->and(HeroMediaData::disabled()->hasVideo())->toBeFalse()
        ->and(HeroMediaData::disabled()->hasImage())->toBeFalse()
        ->and(HeroMediaData::disabled()->poster())->toBeNull();
});

function mediaFor(Theme|Widget|WidgetAsset $model, string $collection, string $fileName, string $mimeType): Media
{
    return Media::factory()
        ->model($model)
        ->state([
            'collection_name' => $collection,
            'file_name' => $fileName,
            'mime_type' => $mimeType,
        ])
        ->create();
}
