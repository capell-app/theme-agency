<?php

declare(strict_types=1);

use Capell\FoundationTheme\Support\ResponsiveAssetLayoutOptions;
use Capell\LayoutBuilder\Enums\ResponsiveLayoutPattern;
use Capell\LayoutBuilder\Models\Widget;

it('resolves configurable grid and carousel layout options from block meta', function (): void {
    $block = new Widget([
        'meta' => [
            'responsive_layout_pattern' => ResponsiveLayoutPattern::DesktopGridMobileCarousel->value,
            'responsive_grid_sm_columns' => 2,
            'responsive_grid_md_columns' => 4,
            'responsive_grid_rows' => 2,
            'responsive_carousel_mobile_slides' => '1.25',
            'responsive_carousel_sm_slides' => '2',
            'responsive_carousel_rows' => 2,
            'responsive_carousel_highlight_active' => true,
            'carousel_loop' => true,
        ],
    ]);

    $options = ResponsiveAssetLayoutOptions::fromBlock($block, 9);

    expect($options->pattern)->toBe(ResponsiveLayoutPattern::DesktopGridMobileCarousel)
        ->and($options->smColumns)->toBe(2)
        ->and($options->mdColumns)->toBe(4)
        ->and($options->gridRows)->toBe(2)
        ->and($options->mobileSlides)->toBe(1.25)
        ->and($options->smSlides)->toBe(2.0)
        ->and($options->carouselRows)->toBe(2)
        ->and($options->carouselAlign())->toBe('center')
        ->and($options->carouselLoop())->toBeFalse()
        ->and($options->carouselBreakpointsJson())->toContain('"slidesPerView":1.25')
        ->and((string) $options->gridRowsStyle('test-grid'))->toContain('#test-grid > :nth-child(n + 9)');
});
