<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Settings;

use Capell\Admin\Filament\Contracts\HasSchema;
use Capell\SeoSuite\Enums\AiDiscoveryCrawlerPolicyEnum;
use Capell\SeoSuite\Enums\SeoCheckModeEnum;
use Capell\SeoSuite\Enums\SeoQualityGatePresetEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SeoSettingsSchema implements HasSchema
{
    public static function make(Schema $configurator): array
    {
        return [
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    Checkbox::make('seo_audit_enabled')
                        ->label(__('capell-seo-suite::form.seo_audit_enabled'))
                        ->helperText(__('capell-seo-suite::form.seo_audit_enabled_helper'))
                        ->default(true)
                        ->reactive(),
                    Grid::make(2)
                        ->columnSpanFull()
                        ->visible(fn (Get $get): bool => $get('seo_audit_enabled') === true)
                        ->schema([
                            Checkbox::make('seo_check_meta_description')
                                ->label(__('capell-seo-suite::form.seo_check_meta_description'))
                                ->default(true),
                            Checkbox::make('seo_check_meta_title')
                                ->label(__('capell-seo-suite::form.seo_check_meta_title'))
                                ->default(true),
                            Checkbox::make('seo_check_duplicate_title')
                                ->label(__('capell-seo-suite::form.seo_check_duplicate_title'))
                                ->default(true),
                        ]),
                    Section::make(__('capell-seo-suite::generic.seo_authoring_quality_gates'))
                        ->columnSpanFull()
                        ->compact()
                        ->collapsible()
                        ->columns(3)
                        ->schema([
                            Checkbox::make('seo_authoring_strict_meta_enabled')
                                ->label(__('capell-seo-suite::form.seo_authoring_strict_meta_enabled'))
                                ->helperText(__('capell-seo-suite::form.seo_authoring_strict_meta_enabled_helper'))
                                ->default(false)
                                ->reactive(),
                            Select::make('seo_quality_gate_preset')
                                ->label(__('capell-seo-suite::form.seo_quality_gate_preset'))
                                ->options(SeoQualityGatePresetEnum::class)
                                ->default(SeoQualityGatePresetEnum::Standard->value)
                                ->afterStateUpdated(
                                    function (Set $set, mixed $state): void {
                                        self::applyPreset($set, $state);
                                    },
                                ),
                            Checkbox::make('seo_authoring_require_title_in_description')
                                ->label(__('capell-seo-suite::form.seo_authoring_require_title_in_description'))
                                ->helperText(__('capell-seo-suite::form.seo_authoring_require_title_in_description_helper'))
                                ->default(false),
                            TextInput::make('seo_meta_description_min_length')
                                ->label(__('capell-seo-suite::form.seo_meta_description_min_length'))
                                ->numeric()
                                ->minValue(1)
                                ->default(65)
                                ->required(),
                            TextInput::make('seo_meta_description_max_length')
                                ->label(__('capell-seo-suite::form.seo_meta_description_max_length'))
                                ->numeric()
                                ->minValue(1)
                                ->default(200)
                                ->required(),
                            Select::make('seo_quality_gate_meta_description_mode')
                                ->label(__('capell-seo-suite::form.seo_quality_gate_meta_description_mode'))
                                ->options(SeoCheckModeEnum::class)
                                ->default(SeoCheckModeEnum::Blocker->value)
                                ->required(),
                            Select::make('seo_quality_gate_meta_title_mode')
                                ->label(__('capell-seo-suite::form.seo_quality_gate_meta_title_mode'))
                                ->options(SeoCheckModeEnum::class)
                                ->default(SeoCheckModeEnum::Warning->value)
                                ->required(),
                            Select::make('seo_quality_gate_social_image_mode')
                                ->label(__('capell-seo-suite::form.seo_quality_gate_social_image_mode'))
                                ->options(SeoCheckModeEnum::class)
                                ->default(SeoCheckModeEnum::Warning->value)
                                ->required(),
                            Select::make('seo_quality_gate_canonical_mode')
                                ->label(__('capell-seo-suite::form.seo_quality_gate_canonical_mode'))
                                ->options(SeoCheckModeEnum::class)
                                ->default(SeoCheckModeEnum::Warning->value)
                                ->required(),
                            Select::make('seo_quality_gate_robots_mode')
                                ->label(__('capell-seo-suite::form.seo_quality_gate_robots_mode'))
                                ->options(SeoCheckModeEnum::class)
                                ->default(SeoCheckModeEnum::Warning->value)
                                ->required(),
                            Select::make('seo_quality_gate_schema_mode')
                                ->label(__('capell-seo-suite::form.seo_quality_gate_schema_mode'))
                                ->options(SeoCheckModeEnum::class)
                                ->default(SeoCheckModeEnum::Warning->value)
                                ->required(),
                        ]),
                    Checkbox::make('ai_discovery_default_enabled')
                        ->label(__('capell-seo-suite::form.ai_discovery_default_enabled'))
                        ->helperText(__('capell-seo-suite::form.ai_discovery_default_enabled_helper'))
                        ->default(true),
                    Checkbox::make('ai_discovery_audit_enabled')
                        ->label(__('capell-seo-suite::form.ai_discovery_audit_enabled'))
                        ->helperText(__('capell-seo-suite::form.ai_discovery_audit_enabled_helper'))
                        ->default(true),
                    Select::make('ai_discovery_crawler_policy')
                        ->label(__('capell-seo-suite::form.ai_discovery_crawler_policy'))
                        ->helperText(__('capell-seo-suite::form.ai_discovery_crawler_policy_helper'))
                        ->options(AiDiscoveryCrawlerPolicyEnum::class)
                        ->default(AiDiscoveryCrawlerPolicyEnum::SearchVisibleTrainingRestricted->value)
                        ->required(),
                    Checkbox::make('pagespeed_audit_enabled')
                        ->label(__('capell-seo-suite::form.pagespeed_audit_enabled'))
                        ->helperText(__('capell-seo-suite::form.pagespeed_audit_enabled_helper'))
                        ->default(true)
                        ->reactive(),
                    Grid::make(3)
                        ->columnSpanFull()
                        ->visible(fn (Get $get): bool => $get('pagespeed_audit_enabled') === true)
                        ->schema([
                            Checkbox::make('pagespeed_weekly_digest_enabled')
                                ->label(__('capell-seo-suite::form.pagespeed_weekly_digest_enabled'))
                                ->helperText(__('capell-seo-suite::form.pagespeed_weekly_digest_enabled_helper'))
                                ->default(true),
                            TextInput::make('pagespeed_scheduled_limit')
                                ->label(__('capell-seo-suite::form.pagespeed_scheduled_limit'))
                                ->numeric()
                                ->minValue(1)
                                ->default(50)
                                ->required(),
                            TextInput::make('pagespeed_stale_after_days')
                                ->label(__('capell-seo-suite::form.pagespeed_stale_after_days'))
                                ->numeric()
                                ->minValue(1)
                                ->default(14)
                                ->required(),
                        ]),
                ]),
        ];
    }

    private static function applyPreset(Set $set, mixed $state): void
    {
        $preset = is_string($state) ? SeoQualityGatePresetEnum::tryFrom($state) : null;

        $values = match ($preset) {
            SeoQualityGatePresetEnum::Relaxed => [
                'seo_authoring_strict_meta_enabled' => false,
                'seo_meta_description_min_length' => 50,
                'seo_meta_description_max_length' => 220,
                'seo_quality_gate_meta_description_mode' => SeoCheckModeEnum::Warning->value,
                'seo_quality_gate_meta_title_mode' => SeoCheckModeEnum::Ignored->value,
                'seo_quality_gate_social_image_mode' => SeoCheckModeEnum::Ignored->value,
                'seo_quality_gate_canonical_mode' => SeoCheckModeEnum::Ignored->value,
                'seo_quality_gate_robots_mode' => SeoCheckModeEnum::Warning->value,
                'seo_quality_gate_schema_mode' => SeoCheckModeEnum::Ignored->value,
            ],
            SeoQualityGatePresetEnum::Strict => [
                'seo_authoring_strict_meta_enabled' => true,
                'seo_meta_description_min_length' => 65,
                'seo_meta_description_max_length' => 200,
                'seo_quality_gate_meta_description_mode' => SeoCheckModeEnum::Blocker->value,
                'seo_quality_gate_meta_title_mode' => SeoCheckModeEnum::Warning->value,
                'seo_quality_gate_social_image_mode' => SeoCheckModeEnum::Warning->value,
                'seo_quality_gate_canonical_mode' => SeoCheckModeEnum::Warning->value,
                'seo_quality_gate_robots_mode' => SeoCheckModeEnum::Warning->value,
                'seo_quality_gate_schema_mode' => SeoCheckModeEnum::Warning->value,
            ],
            SeoQualityGatePresetEnum::Agency => [
                'seo_authoring_strict_meta_enabled' => true,
                'seo_meta_description_min_length' => 120,
                'seo_meta_description_max_length' => 160,
                'seo_quality_gate_meta_description_mode' => SeoCheckModeEnum::Blocker->value,
                'seo_quality_gate_meta_title_mode' => SeoCheckModeEnum::Blocker->value,
                'seo_quality_gate_social_image_mode' => SeoCheckModeEnum::Blocker->value,
                'seo_quality_gate_canonical_mode' => SeoCheckModeEnum::Warning->value,
                'seo_quality_gate_robots_mode' => SeoCheckModeEnum::Blocker->value,
                'seo_quality_gate_schema_mode' => SeoCheckModeEnum::Warning->value,
            ],
            default => [
                'seo_authoring_strict_meta_enabled' => false,
                'seo_meta_description_min_length' => 65,
                'seo_meta_description_max_length' => 200,
                'seo_quality_gate_meta_description_mode' => SeoCheckModeEnum::Blocker->value,
                'seo_quality_gate_meta_title_mode' => SeoCheckModeEnum::Warning->value,
                'seo_quality_gate_social_image_mode' => SeoCheckModeEnum::Warning->value,
                'seo_quality_gate_canonical_mode' => SeoCheckModeEnum::Warning->value,
                'seo_quality_gate_robots_mode' => SeoCheckModeEnum::Warning->value,
                'seo_quality_gate_schema_mode' => SeoCheckModeEnum::Warning->value,
            ],
        };

        foreach ($values as $key => $value) {
            $set($key, $value);
        }

    }
}
