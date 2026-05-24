<?php

declare(strict_types=1);

return [
    'hero_background' => [
        'label' => 'Hero background',
        'mode' => 'Hero background',
        'background_color' => 'Background colour',
        'accent_color' => 'Accent colour',
        'accent_color_alt' => 'Second accent',
        'overlay_style' => 'Overlay style',
        'overlay_opacity' => 'Overlay strength',
        'theme_helper' => 'Pages using this theme inherit this hero background unless the widget or widget asset overrides it.',
        'block_helper' => 'This widget uses the site theme hero background by default. Choose custom to override it here, or off to disable the decorative layer.',
        'asset_helper' => 'This widget asset inherits the widget or theme hero background by default. Choose off when the asset image should stand alone.',
        'mode_options' => [
            'inherit' => 'Use theme/widget',
            'default' => 'Default overlay',
            'custom' => 'Custom overlay',
            'off' => 'Off',
        ],
        'overlay_options' => [
            'mesh' => 'Soft mesh',
            'ribbons' => 'Ribbon lines',
            'grid' => 'Subtle grid',
            'contours' => 'Contours',
        ],
    ],
];
