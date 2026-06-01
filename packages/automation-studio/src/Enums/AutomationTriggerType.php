<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Enums;

use Filament\Support\Contracts\HasLabel;
use Override;

enum AutomationTriggerType: string implements HasLabel
{
    case FormSubmitted = 'form_submitted';
    case AccessApproved = 'access_approved';
    case PagePublished = 'page_published';
    case CampaignConverted = 'campaign_converted';

    #[Override]
    public function getLabel(): string
    {
        return match ($this) {
            self::FormSubmitted => __('capell-automation-studio::generic.triggers.form_submitted'),
            self::AccessApproved => __('capell-automation-studio::generic.triggers.access_approved'),
            self::PagePublished => __('capell-automation-studio::generic.triggers.page_published'),
            self::CampaignConverted => __('capell-automation-studio::generic.triggers.campaign_converted'),
        };
    }
}
