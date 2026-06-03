<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Filament\Resources\CampaignCtaWidgets\Schemas;

use Capell\Admin\Data\Configurators\ConfiguratorContextData;
use Capell\Admin\Filament\Contracts\FormConfigurator;
use Capell\Admin\Support\SiteScope;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

final class CampaignCtaWidgetForm implements FormConfigurator
{
    public static function configure(Schema $configurator, ?ConfiguratorContextData $context = null): Schema
    {
        return $configurator
            ->columns(['default' => 1, 'lg' => 2])
            ->schema([
                Section::make(__('capell-campaign-studio::generic.cta_form_sections.details'))
                    ->columns(['default' => 1, 'lg' => 2])
                    ->schema([
                        Select::make('campaign_group_id')
                            ->label(__('capell-campaign-studio::form.campaign_group'))
                            ->relationship('campaignGroup', 'name', modifyQueryUsing: fn (Builder $query): Builder => SiteScope::applyForCurrentActor($query))
                            ->required(),
                        TextInput::make('site_id')
                            ->label(__('capell-campaign-studio::form.site'))
                            ->numeric(),
                        TextInput::make('name')
                            ->label(__('capell-campaign-studio::form.name'))
                            ->required(),
                        TextInput::make('key')
                            ->label(__('capell-campaign-studio::form.key'))
                            ->required(),
                        TextInput::make('headline')
                            ->label(__('capell-campaign-studio::form.headline')),
                        Textarea::make('body')
                            ->label(__('capell-campaign-studio::form.body'))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make(__('capell-campaign-studio::generic.cta_form_sections.actions'))
                    ->schema([
                        Repeater::make('actions')
                            ->label(__('capell-campaign-studio::form.actions'))
                            ->columnSpanFull()
                            ->defaultItems(0)
                            ->cloneable()
                            ->reorderable()
                            ->addActionLabel(__('capell-campaign-studio::form.add_action'))
                            ->itemLabel(fn (array $state): ?string => is_string($state['label'] ?? null) ? $state['label'] : null)
                            ->schema([
                                Grid::make(['default' => 1, 'lg' => 2])
                                    ->schema([
                                        TextInput::make('label')
                                            ->label(__('capell-campaign-studio::form.action_label'))
                                            ->required(),
                                        Select::make('style')
                                            ->label(__('capell-campaign-studio::form.action_style'))
                                            ->options([
                                                'primary' => __('capell-campaign-studio::generic.action_styles.primary'),
                                                'secondary' => __('capell-campaign-studio::generic.action_styles.secondary'),
                                            ])
                                            ->default('primary')
                                            ->required(),
                                    ]),
                                TextInput::make('url')
                                    ->label(__('capell-campaign-studio::form.action_url'))
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('goal_key')
                                    ->label(__('capell-campaign-studio::form.goal_key'))
                                    ->columnSpanFull(),
                                Fieldset::make(__('capell-campaign-studio::form.utm_parameters'))
                                    ->statePath('utm')
                                    ->columns(['default' => 1, 'lg' => 2])
                                    ->schema([
                                        TextInput::make('source')
                                            ->label(__('capell-campaign-studio::form.utm_source')),
                                        TextInput::make('medium')
                                            ->label(__('capell-campaign-studio::form.utm_medium')),
                                        TextInput::make('campaign')
                                            ->label(__('capell-campaign-studio::form.utm_campaign')),
                                        TextInput::make('term')
                                            ->label(__('capell-campaign-studio::form.utm_term')),
                                        TextInput::make('content')
                                            ->label(__('capell-campaign-studio::form.utm_content')),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make(__('capell-campaign-studio::generic.cta_form_sections.default_utm'))
                    ->schema([
                        Fieldset::make(__('capell-campaign-studio::form.default_utm'))
                            ->statePath('default_utm')
                            ->columns(['default' => 1, 'lg' => 2])
                            ->schema([
                                TextInput::make('source')
                                    ->label(__('capell-campaign-studio::form.utm_source')),
                                TextInput::make('medium')
                                    ->label(__('capell-campaign-studio::form.utm_medium')),
                                TextInput::make('campaign')
                                    ->label(__('capell-campaign-studio::form.utm_campaign')),
                                TextInput::make('term')
                                    ->label(__('capell-campaign-studio::form.utm_term')),
                                TextInput::make('content')
                                    ->label(__('capell-campaign-studio::form.utm_content')),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make(__('capell-campaign-studio::generic.cta_form_sections.status'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('capell-campaign-studio::form.is_active')),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
