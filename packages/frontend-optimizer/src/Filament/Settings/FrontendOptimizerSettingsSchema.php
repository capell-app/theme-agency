<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Filament\Settings;

use Capell\Admin\Filament\Contracts\HasSchema;
use Capell\Admin\Filament\Support\HelperText;
use Capell\FrontendOptimizer\Enums\OptimizationScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class FrontendOptimizerSettingsSchema implements HasSchema
{
    public static function make(Schema $configurator): array
    {
        return [
            Section::make(__('capell-frontend-optimizer::settings.critical_css'))
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    HelperText::apply(
                        Toggle::make('enable_critical_css')
                            ->label(__('capell-frontend-optimizer::settings.enable_critical_css')),
                        'capell-frontend-optimizer::settings.enable_critical_css_helper',
                    ),
                    HelperText::apply(
                        Toggle::make('automatic_generation')
                            ->label(__('capell-frontend-optimizer::settings.automatic_generation')),
                        'capell-frontend-optimizer::settings.automatic_generation_helper',
                    ),
                    Select::make('profile_scope')
                        ->label(__('capell-frontend-optimizer::settings.profile_scope'))
                        ->options(collect(OptimizationScope::cases())->mapWithKeys(
                            static fn (OptimizationScope $scope): array => [$scope->value => $scope->name],
                        )->all())
                        ->default(OptimizationScope::Layout->value),
                    TextInput::make('max_inline_css_bytes')
                        ->label(__('capell-frontend-optimizer::settings.max_inline_css_bytes'))
                        ->integer()
                        ->minValue(1),
                    TagsInput::make('viewports')
                        ->label(__('capell-frontend-optimizer::settings.viewports'))
                        ->helperText(__('capell-frontend-optimizer::settings.viewports_helper'))
                        ->columnSpanFull(),
                    TextInput::make('fold_multiplier')
                        ->label(__('capell-frontend-optimizer::settings.fold_multiplier'))
                        ->numeric()
                        ->minValue(0.1),
                    TextInput::make('extra_fold_pixels')
                        ->label(__('capell-frontend-optimizer::settings.extra_fold_pixels'))
                        ->integer()
                        ->minValue(0),
                    Select::make('playwright_wait_strategy')
                        ->label(__('capell-frontend-optimizer::settings.playwright_wait_strategy'))
                        ->options([
                            'load' => 'load',
                            'domcontentloaded' => 'domcontentloaded',
                            'networkidle' => 'networkidle',
                        ]),
                    TextInput::make('playwright_timeout')
                        ->label(__('capell-frontend-optimizer::settings.playwright_timeout'))
                        ->integer()
                        ->minValue(1)
                        ->suffix(__('capell-admin::form.seconds')),
                ]),
        ];
    }
}
