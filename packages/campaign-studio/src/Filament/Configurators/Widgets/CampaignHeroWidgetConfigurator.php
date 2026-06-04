<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Filament\Configurators\Widgets;

use Capell\LayoutBuilder\Filament\Configurators\Widgets\DefaultWidgetConfigurator;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs\Tab;
use Override;

final class CampaignHeroWidgetConfigurator extends DefaultWidgetConfigurator
{
    #[Override]
    protected function detailsTab(): Tab
    {
        return Tab::make('campaign_hero')
            ->label(__('capell-campaign-studio::generic.campaign'))
            ->schema([
                TextInput::make('meta.eyebrow')
                    ->label(__('capell-campaign-studio::form.eyebrow')),
                TextInput::make('meta.primary_button_text')
                    ->label(__('capell-layout-builder::form.primary_button_text')),
                TextInput::make('meta.primary_button_url')
                    ->label(__('capell-layout-builder::form.primary_button_url')),
                TextInput::make('meta.secondary_button_text')
                    ->label(__('capell-layout-builder::form.secondary_button_text')),
                TextInput::make('meta.secondary_button_url')
                    ->label(__('capell-layout-builder::form.secondary_button_url')),
                TextInput::make('meta.goal_key')
                    ->label(__('capell-campaign-studio::form.primary_goal')),
                TextInput::make('meta.utm_source')
                    ->label(__('capell-campaign-studio::form.utm_source')),
                TextInput::make('meta.utm_medium')
                    ->label(__('capell-campaign-studio::form.utm_medium')),
                TextInput::make('meta.utm_campaign')
                    ->label(__('capell-campaign-studio::form.utm_campaign')),
                TextInput::make('meta.utm_term')
                    ->label(__('capell-campaign-studio::form.utm_term')),
                TextInput::make('meta.utm_content')
                    ->label(__('capell-campaign-studio::form.utm_content')),
            ]);
    }
}
