<?php

declare(strict_types=1);

namespace Capell\Hero\Filament\Components\Forms;

use Capell\Hero\Data\HeroBackgroundData;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;

final class HeroBackgroundSchema
{
    /**
     * @return array<int, mixed>
     */
    public static function theme(): array
    {
        return [
            self::fieldset([
                self::modeSelect([
                    HeroBackgroundData::ModeDefault => __('capell-hero::form.hero_background.mode_options.default'),
                    HeroBackgroundData::ModeCustom => __('capell-hero::form.hero_background.mode_options.custom'),
                    HeroBackgroundData::ModeOff => __('capell-hero::form.hero_background.mode_options.off'),
                ], HeroBackgroundData::ModeDefault)
                    ->helperText(__('capell-hero::form.hero_background.theme_helper')),
            ]),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function block(): array
    {
        return [
            self::fieldset([
                self::modeSelect([
                    HeroBackgroundData::ModeInherit => __('capell-hero::form.hero_background.mode_options.inherit'),
                    HeroBackgroundData::ModeCustom => __('capell-hero::form.hero_background.mode_options.custom'),
                    HeroBackgroundData::ModeOff => __('capell-hero::form.hero_background.mode_options.off'),
                ], HeroBackgroundData::ModeInherit)
                    ->helperText(__('capell-hero::form.hero_background.block_helper')),
            ]),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function asset(): array
    {
        return [
            self::fieldset([
                self::modeSelect([
                    HeroBackgroundData::ModeInherit => __('capell-hero::form.hero_background.mode_options.inherit'),
                    HeroBackgroundData::ModeCustom => __('capell-hero::form.hero_background.mode_options.custom'),
                    HeroBackgroundData::ModeOff => __('capell-hero::form.hero_background.mode_options.off'),
                ], HeroBackgroundData::ModeInherit)
                    ->helperText(__('capell-hero::form.hero_background.asset_helper')),
            ], withMetaStatePath: true),
        ];
    }

    /**
     * @param  array<int, mixed>  $extraModeComponents
     */
    private static function fieldset(array $extraModeComponents, bool $withMetaStatePath = false): Fieldset
    {
        $modeSelect = $extraModeComponents[0] ?? null;

        $fieldset = Fieldset::make(__('capell-hero::form.hero_background.label'))
            ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
            ->schema([
                $modeSelect,
                ColorPicker::make('hero_background.background_color')
                    ->label(__('capell-hero::form.hero_background.background_color'))
                    ->default('#f4f7fb')
                    ->visible(fn (Get $get): bool => $get('hero_background.mode') === HeroBackgroundData::ModeCustom),
                ColorPicker::make('hero_background.accent_color')
                    ->label(__('capell-hero::form.hero_background.accent_color'))
                    ->default('#315f8f')
                    ->visible(fn (Get $get): bool => $get('hero_background.mode') === HeroBackgroundData::ModeCustom),
                ColorPicker::make('hero_background.accent_color_alt')
                    ->label(__('capell-hero::form.hero_background.accent_color_alt'))
                    ->default('#7aa0c4')
                    ->visible(fn (Get $get): bool => $get('hero_background.mode') === HeroBackgroundData::ModeCustom),
                Select::make('hero_background.overlay_style')
                    ->label(__('capell-hero::form.hero_background.overlay_style'))
                    ->default('mesh')
                    ->options([
                        'mesh' => __('capell-hero::form.hero_background.overlay_options.mesh'),
                        'ribbons' => __('capell-hero::form.hero_background.overlay_options.ribbons'),
                        'grid' => __('capell-hero::form.hero_background.overlay_options.grid'),
                        'contours' => __('capell-hero::form.hero_background.overlay_options.contours'),
                    ])
                    ->visible(fn (Get $get): bool => $get('hero_background.mode') === HeroBackgroundData::ModeCustom),
                Select::make('hero_background.overlay_opacity')
                    ->label(__('capell-hero::form.hero_background.overlay_opacity'))
                    ->default('0.32')
                    ->options([
                        '0.16' => '16%',
                        '0.24' => '24%',
                        '0.32' => '32%',
                        '0.44' => '44%',
                        '0.56' => '56%',
                    ])
                    ->visible(fn (Get $get): bool => $get('hero_background.mode') === HeroBackgroundData::ModeCustom),
            ]);

        return $withMetaStatePath ? $fieldset->statePath('meta') : $fieldset;
    }

    /**
     * @param  array<int|string, string>  $options
     */
    private static function modeSelect(array $options, string $default): Select
    {
        return Select::make('hero_background.mode')
            ->label(__('capell-hero::form.hero_background.mode'))
            ->default($default)
            ->options($options)
            ->live();
    }
}
