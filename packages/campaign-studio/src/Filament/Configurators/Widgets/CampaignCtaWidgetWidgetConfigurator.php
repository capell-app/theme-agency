<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Filament\Configurators\Widgets;

use Capell\Admin\Support\SiteScope;
use Capell\CampaignStudio\Models\CampaignCtaWidget;
use Capell\LayoutBuilder\Filament\Configurators\Widgets\DefaultWidgetConfigurator;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs\Tab;
use Override;

final class CampaignCtaWidgetWidgetConfigurator extends DefaultWidgetConfigurator
{
    #[Override]
    protected function detailsTab(): Tab
    {
        return Tab::make('campaign_cta')
            ->label(__('capell-campaign-studio::generic.cta_widget'))
            ->schema([
                Select::make('meta.cta_layout_widget_id')
                    ->label(__('capell-campaign-studio::form.cta_widget'))
                    ->options(fn (): array => SiteScope::applyForCurrentActor(CampaignCtaWidget::query())
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload(),
            ]);
    }
}
