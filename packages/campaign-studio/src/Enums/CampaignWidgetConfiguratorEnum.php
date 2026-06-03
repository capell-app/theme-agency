<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Enums;

use Capell\CampaignStudio\Filament\Configurators\Widgets\CampaignCtaWidgetWidgetConfigurator;
use Capell\CampaignStudio\Filament\Configurators\Widgets\CampaignHeroWidgetConfigurator;
use Capell\CampaignStudio\Filament\Configurators\Widgets\CampaignLeadFormWidgetConfigurator;
use Filament\Support\Contracts\HasLabel;

enum CampaignWidgetConfiguratorEnum: string implements HasLabel
{
    case CampaignHero = CampaignHeroWidgetConfigurator::class;
    case CampaignCtaWidget = CampaignCtaWidgetWidgetConfigurator::class;
    case CampaignLeadForm = CampaignLeadFormWidgetConfigurator::class;

    public function getLabel(): string
    {
        return match ($this->name) {
            self::CampaignHero->name => __('capell-campaign-studio::generic.campaign_widget_components.campaign_hero'),
            self::CampaignCtaWidget->name => __('capell-campaign-studio::generic.campaign_widget_components.campaign_cta_widget'),
            self::CampaignLeadForm->name => __('capell-campaign-studio::generic.campaign_widget_components.campaign_lead_form'),
        };
    }
}
