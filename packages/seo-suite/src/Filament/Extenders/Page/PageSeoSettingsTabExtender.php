<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Extenders\Page;

use Capell\Admin\Contracts\Extenders\PageSchemaExtender;
use Capell\Admin\Enums\PageTranslationSchemaHookEnum;
use Capell\Admin\Filament\Components\Forms\CacheTimeSelect;
use Capell\Admin\Filament\Components\Forms\Page\TranslationsRepeater;
use Capell\Admin\Filament\Components\Forms\PageSelect;
use Capell\Admin\Filament\Support\HelperText;
use Capell\Core\Models\Page;
use Capell\SeoSuite\Enums\RobotsDirectiveEnum;
use Capell\SeoSuite\Filament\Components\Forms\Page\PageSeoPanel;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class PageSeoSettingsTabExtender implements PageSchemaExtender
{
    /**
     * @return array<int, Component>
     */
    public function extendSidebarComponents(Schema $configurator): array
    {
        return [];
    }

    /**
     * @return array<int, Component>
     */
    public function extendTranslationComponentsForHook(Schema $configurator, PageTranslationSchemaHookEnum $hook): array
    {
        return [];
    }

    /**
     * @param  array<int, mixed>  $relationManagers
     * @return array<int, mixed>
     */
    public function extendRelationManagers(Model $record, array $relationManagers): array
    {
        return $relationManagers;
    }

    /**
     * @param  array<int, mixed>  $tabs
     * @return array<int, mixed>
     */
    public function extendTabs(Schema $configurator, array $tabs): array
    {
        $record = $configurator->getRecord();

        if ($record instanceof Model && ! $record instanceof Page) {
            return $tabs;
        }

        $tabs[] = Tab::make(__('capell-seo-suite::generic.seo_settings'))
            ->key('seo-settings')
            ->icon(Heroicon::OutlinedArrowTrendingUp)
            ->columns()
            ->schema([
                Livewire::make(
                    'capell-seo-suite.edit-page-audit-tabs',
                    fn (?Page $record = null): array => ['record' => $record],
                )
                    ->lazy()
                    ->columnSpanFull(),
                $this->getTranslationSeoSection(),
                $this->getSeoSettingsSection(),
            ]);

        return $tabs;
    }

    private function getTranslationSeoSection(): TranslationsRepeater
    {
        return TranslationsRepeater::make('translations')
            ->columnSpanFull()
            ->schema([
                $this->getSearchMetaSection(),
                PageSeoPanel::make(),
            ])
            ->contained(fn (string $operation): bool => in_array($operation, ['create', 'edit'], true));
    }

    private function getSearchMetaSection(): Section
    {
        return Section::make(__('capell-admin::tab.seo_settings'))
            ->statePath('meta')
            ->collapsed()
            ->compact()
            ->columns()
            ->columnSpanFull()
            ->schema([
                TextInput::make('title')
                    ->label(__('capell-admin::form.meta_title.label'))
                    ->helperText(__('capell-admin::form.meta_title.helper'))
                    ->placeholder(':site')
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(__('capell-admin::form.meta_description.label'))
                    ->helperText(__('capell-admin::form.meta_description.helper'))
                    ->rows(3)
                    ->maxLength(320),
                TextInput::make('keywords')
                    ->label(__('capell-admin::form.meta_keywords.label'))
                    ->helperText(__('capell-admin::form.meta_keywords.helper'))
                    ->maxLength(255),
            ]);
    }

    private function getSeoSettingsSection(): Section
    {
        return Section::make(__('capell-seo-suite::generic.seo_settings'))
            ->collapsible()
            ->compact()
            ->columnSpanFull()
            ->statePath('meta')
            ->icon(Heroicon::OutlinedArrowTrendingUp)
            ->columns(3)
            ->schema([
                PageSelect::make('canonical_page_id')
                    ->pageGroup('page')
                    ->label(__('capell-seo-suite::form.canonical_page'))
                    ->helperText(__('capell-seo-suite::generic.canonical_page_info'))
                    ->withHintEditAction()
                    ->dehydrated()
                    ->reactive(),
                CacheTimeSelect::make('cache_time'),
                Select::make('priority')
                    ->label(__('capell-seo-suite::form.priority'))
                    ->options(
                        collect(range(0, 9))
                            ->map(fn (int $priorityIndex): float => round(1.0 - $priorityIndex * 0.1, 1))
                            ->filter(fn (float $value): bool => $value >= 0.1)
                            ->mapWithKeys(function (float $value): array {
                                $formatted = number_format($value, 1);
                                if ($formatted === '1.0') {
                                    $label = $formatted . ' ' . __('capell-seo-suite::generic.highest');
                                } elseif ($formatted === '0.1') {
                                    $label = $formatted . ' ' . __('capell-seo-suite::generic.lowest');
                                } else {
                                    $label = $formatted;
                                }

                                return [$formatted => $label];
                            }),
                    ),
                HelperText::apply(
                    TextInput::make('canonical_url')
                        ->label(__('capell-seo-suite::form.canonical_url.label'))
                        ->url()
                        ->placeholder('https://...'),
                    'capell-seo-suite::form.canonical_url.helper',
                ),
                CheckboxList::make('robots')
                    ->options($this->robotsOptions())
                    ->descriptions($this->robotsDescriptions())
                    ->columnSpan(2)
                    ->default([])
                    ->mutateStateForValidationUsing(fn (mixed $state): array => $this->normalizeRobotsState($state))
                    ->dehydrateStateUsing(fn (mixed $state): array => $this->normalizeRobotsState($state))
                    ->afterStateHydrated(function (CheckboxList $component, mixed $state): void {
                        $component->state($this->normalizeRobotsState($state));
                    }),
                Textarea::make('meta_tags')
                    ->columnSpan(2)
                    ->rows(4)
                    ->label(__('capell-seo-suite::form.meta_tags'))
                    ->hint(__('capell-seo-suite::generic.meta_tags_extra')),
                Section::make(__('capell-seo-suite::generic.ai_discovery'))
                    ->compact()
                    ->collapsible()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Checkbox::make('ai_discovery.include_in_ai_index')
                            ->label(__('capell-seo-suite::form.ai_discovery_include_in_ai_index'))
                            ->default(true),
                        TextInput::make('ai_discovery.section')
                            ->label(__('capell-seo-suite::form.ai_discovery_section'))
                            ->placeholder('Pages'),
                        TextInput::make('ai_discovery.priority')
                            ->label(__('capell-seo-suite::form.ai_discovery_priority'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->default(500),
                        Textarea::make('ai_discovery.summary')
                            ->label(__('capell-seo-suite::form.ai_discovery_summary'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('ai_discovery.markdown_override')
                            ->label(__('capell-seo-suite::form.ai_discovery_markdown_override'))
                            ->rows(6)
                            ->columnSpanFull(),
                        TextInput::make('ai_discovery.exclude_reason')
                            ->label(__('capell-seo-suite::form.ai_discovery_exclude_reason'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    private function robotsOptions(): array
    {
        return collect(RobotsDirectiveEnum::cases())
            ->mapWithKeys(fn (RobotsDirectiveEnum $directive): array => [$directive->value => $directive->getLabel()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function robotsDescriptions(): array
    {
        return collect(RobotsDirectiveEnum::cases())
            ->mapWithKeys(fn (RobotsDirectiveEnum $directive): array => [$directive->value => $directive->getDescription()])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function normalizeRobotsState(mixed $state): array
    {
        if (is_string($state)) {
            $state = array_map(trim(...), explode(',', $state));
        }

        if (! is_array($state)) {
            return [];
        }

        $validDirectives = array_flip(array_keys($this->robotsOptions()));
        $directives = [];

        foreach ($state as $key => $value) {
            $directive = is_string($key) ? $key : $value;

            if (is_string($key) && $value !== true) {
                continue;
            }

            if (! is_string($directive)) {
                continue;
            }

            if (! isset($validDirectives[$directive])) {
                continue;
            }

            $directives[] = $directive;
        }

        return array_values(array_unique($directives));
    }
}
