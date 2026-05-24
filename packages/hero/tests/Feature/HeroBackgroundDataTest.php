<?php

declare(strict_types=1);

use Capell\Core\Models\Theme;
use Capell\Hero\Actions\ResolveHeroBackgroundDataAction;
use Capell\LayoutBuilder\Models\Block;
use Capell\LayoutBuilder\Models\BlockAsset;

it('resolves hero background from theme block and asset layers', function (): void {
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
    $block = Block::factory()->create([
        'meta' => [
            'hero_background' => [
                'mode' => 'custom',
                'background_color' => '#f8f3e8',
                'overlay_style' => 'contours',
            ],
        ],
    ]);
    $asset = BlockAsset::factory()->create([
        'meta' => [
            'hero_background' => [
                'mode' => 'custom',
                'accent_color' => '#7251a3',
            ],
        ],
    ]);

    $background = ResolveHeroBackgroundDataAction::run($theme, $block, $asset);

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
    $block = Block::factory()->create([
        'meta' => [
            'hero_background' => [
                'mode' => 'inherit',
            ],
        ],
    ]);
    $asset = BlockAsset::factory()->create([
        'meta' => [
            'hero_background' => [
                'mode' => 'off',
            ],
        ],
    ]);

    expect(ResolveHeroBackgroundDataAction::run($theme, $block, $asset)->enabled)->toBeFalse();
});
