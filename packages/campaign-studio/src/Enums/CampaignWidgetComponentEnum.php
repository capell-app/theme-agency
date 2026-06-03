<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Enums;

use Filament\Support\Contracts\HasLabel;

enum CampaignWidgetComponentEnum: string implements HasLabel
{
    case CampaignHero = 'capell-campaign-studio::components.widget.campaign-hero';
    case CampaignCtaWidget = 'capell-campaign-studio::components.widget.campaign-cta-widget';
    case CampaignLeadForm = 'capell-campaign-studio::components.widget.campaign-lead-form';

    public function getLabel(): string
    {
        return match ($this->name) {
            self::CampaignHero->name => __('capell-campaign-studio::generic.campaign_widget_components.campaign_hero'),
            self::CampaignCtaWidget->name => __('capell-campaign-studio::generic.campaign_widget_components.campaign_cta_widget'),
            self::CampaignLeadForm->name => __('capell-campaign-studio::generic.campaign_widget_components.campaign_lead_form'),
        };
    }
}
