<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Filament\Configurators\Types;

use Capell\Admin\Filament\Components\Forms\CacheFrequencySelect;
use Capell\Admin\Filament\Components\Forms\CacheTimeSelect;
use Capell\Admin\Filament\Components\Forms\ComponentSelect;
use Capell\Admin\Filament\Configurators\Blueprints\PageBlueprintConfigurator;
use Capell\Core\Enums\ComponentTypeEnum;
use Capell\Core\Models\Blueprint;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Override;

final class FrontendOptimizerPageTypeConfigurator extends PageBlueprintConfigurator
{
    #[Override]
    protected function frontendTab(): Tab
    {
        return Tab::make(__('capell-admin::generic.frontend'))
            ->key('frontend')
            ->icon('heroicon-m-cog-6-tooth')
            ->columns()
            ->schema([
                Section::make(__('capell-admin::form.rendering'))
                    ->description(__('capell-admin::generic.type_rendering_description'))
                    ->compact()
                    ->columnSpanFull()
                    ->schema([
                        ComponentSelect::make('component')
                            ->setupType(ComponentTypeEnum::Page)
                            ->withCreateComponentAction()
                            ->withSourceFlow(),
                        CacheTimeSelect::make('meta.cache_time'),
                        CacheFrequencySelect::make('meta.cache_frequency'),
                    ]),
                Section::make(__('capell-admin::form.page_type_behaviour'))
                    ->description(__('capell-admin::generic.type_behaviour_description'))
                    ->compact()
                    ->columnSpanFull()
                    ->statePath('meta')
                    ->columns()
                    ->schema([
                        Checkbox::make('disable_visit_logs')
                            ->label(__('capell-admin::form.disable_visit_logs')),
                        Checkbox::make('frontend_optimizer.disable_critical_css')
                            ->label(__('capell-frontend-optimizer::settings.disable_critical_css')),
                        Checkbox::make('accessible')
                            ->label(__('capell-admin::form.accessible'))
                            ->helperText(__('capell-admin::generic.page_accessible_info'))
                            ->afterStateHydrated(function (Checkbox $component, ?Blueprint $record): void {
                                if ($record instanceof Blueprint && $record->getMeta('accessible') === null) {
                                    $component->state(true);
                                }
                            })
                            ->default(true),
                        Checkbox::make('listable')
                            ->label(__('capell-admin::form.listable'))
                            ->helperText(__('capell-admin::generic.page_listable_info'))
                            ->afterStateHydrated(function (Checkbox $component, ?Blueprint $record): void {
                                if ($record instanceof Blueprint && $record->getMeta('listable') === null) {
                                    $component->state(true);
                                }
                            })
                            ->default(true),
                        Checkbox::make('sitemap')
                            ->label(__('capell-admin::form.sitemap'))
                            ->helperText(__('capell-admin::generic.page_sitemap_info'))
                            ->afterStateHydrated(function (Checkbox $component, ?Blueprint $record): void {
                                if ($record instanceof Blueprint && $record->getMeta('sitemap') === null) {
                                    $component->state(true);
                                }
                            })
                            ->default(true),
                        Checkbox::make('with_next_prev')
                            ->label(__('capell-admin::form.next_prev')),
                        Toggle::make('layout_editable')
                            ->label(__('capell-admin::form.layout_editable'))
                            ->afterStateHydrated(function (Toggle $component, ?Blueprint $record): void {
                                if ($record instanceof Blueprint && $record->getMeta('layout_editable') === null) {
                                    $component->state(true);
                                }
                            })
                            ->default(true),
                    ]),
            ]);
    }
}
