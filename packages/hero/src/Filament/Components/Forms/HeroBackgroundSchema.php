<?php

declare(strict_types=1);

namespace Capell\Hero\Filament\Components\Forms;

use Capell\Admin\Filament\Components\Forms\MediaLibraryFileUpload;
use Capell\Hero\Data\HeroBackgroundData;
use Capell\Hero\Data\HeroMediaData;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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
            self::mediaSettingsFieldset(HeroMediaData::ModeOff),
            self::mediaUploadsFieldset(),
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
            self::mediaSettingsFieldset(HeroMediaData::ModeInherit),
            self::mediaUploadsFieldset(),
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
            self::mediaSettingsFieldset(HeroMediaData::ModeInherit, withMetaStatePath: true),
            self::mediaUploadsFieldset(),
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

    private static function mediaSettingsFieldset(string $defaultMode, bool $withMetaStatePath = false): Fieldset
    {
        $fieldset = Fieldset::make(__('capell-hero::form.hero_media.label'))
            ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
            ->schema([
                Select::make('hero_media.mode')
                    ->label(__('capell-hero::form.hero_media.mode'))
                    ->default($defaultMode)
                    ->options([
                        HeroMediaData::ModeInherit => __('capell-hero::form.hero_media.mode_options.inherit'),
                        HeroMediaData::ModeCustom => __('capell-hero::form.hero_media.mode_options.custom'),
                        HeroMediaData::ModeOff => __('capell-hero::form.hero_media.mode_options.off'),
                    ])
                    ->live(),
                Checkbox::make('hero_media.autoplay')
                    ->label(__('capell-hero::form.hero_media.autoplay'))
                    ->default(true)
                    ->visible(fn (Get $get): bool => $get('hero_media.mode') === HeroMediaData::ModeCustom),
                Checkbox::make('hero_media.loop')
                    ->label(__('capell-hero::form.hero_media.loop'))
                    ->default(true)
                    ->visible(fn (Get $get): bool => $get('hero_media.mode') === HeroMediaData::ModeCustom),
                Checkbox::make('hero_media.muted')
                    ->label(__('capell-hero::form.hero_media.muted'))
                    ->default(true)
                    ->visible(fn (Get $get): bool => $get('hero_media.mode') === HeroMediaData::ModeCustom),
                Checkbox::make('hero_media.pause_when_out_of_view')
                    ->label(__('capell-hero::form.hero_media.pause_when_out_of_view'))
                    ->default(true)
                    ->visible(fn (Get $get): bool => $get('hero_media.mode') === HeroMediaData::ModeCustom),
                Select::make('hero_media.preload')
                    ->label(__('capell-hero::form.hero_media.preload'))
                    ->default(HeroMediaData::PreloadMetadata)
                    ->options([
                        HeroMediaData::PreloadNone => __('capell-hero::form.hero_media.preload_options.none'),
                        HeroMediaData::PreloadMetadata => __('capell-hero::form.hero_media.preload_options.metadata'),
                        HeroMediaData::PreloadAuto => __('capell-hero::form.hero_media.preload_options.auto'),
                    ])
                    ->visible(fn (Get $get): bool => $get('hero_media.mode') === HeroMediaData::ModeCustom),
            ]);

        return $withMetaStatePath ? $fieldset->statePath('meta') : $fieldset;
    }

    private static function mediaUploadsFieldset(): Fieldset
    {
        return Fieldset::make(__('capell-hero::form.hero_media.uploads_label'))
            ->columns(['default' => 1, 'lg' => 3])
            ->schema([
                self::videoUpload(HeroMediaData::CollectionDesktopVideo, __('capell-hero::form.hero_media.desktop_video')),
                self::videoUpload(HeroMediaData::CollectionTabletVideo, __('capell-hero::form.hero_media.tablet_video')),
                self::videoUpload(HeroMediaData::CollectionMobileVideo, __('capell-hero::form.hero_media.mobile_video')),
                self::imageUpload(HeroMediaData::CollectionDesktopImage, __('capell-hero::form.hero_media.desktop_image')),
                self::imageUpload(HeroMediaData::CollectionTabletImage, __('capell-hero::form.hero_media.tablet_image')),
                self::imageUpload(HeroMediaData::CollectionMobileImage, __('capell-hero::form.hero_media.mobile_image')),
            ]);
    }

    private static function videoUpload(string $collection, string $label): Field
    {
        $component = self::mediaUpload($collection)
            ->label($label);

        if ($component instanceof SpatieMediaLibraryFileUpload) {
            $component
                ->acceptedFileTypes(['video/mp4', 'video/webm'])
                ->collection($collection);
        }

        return $component;
    }

    private static function imageUpload(string $collection, string $label): Field
    {
        $component = self::mediaUpload($collection)
            ->label($label);

        if ($component instanceof SpatieMediaLibraryFileUpload) {
            $component
                ->image()
                ->collection($collection);
        }

        return $component;
    }

    private static function mediaUpload(string $collection): Field
    {
        return MediaLibraryFileUpload::make($collection);
    }
}
