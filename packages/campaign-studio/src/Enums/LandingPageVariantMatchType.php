<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Enums;

use Filament\Support\Contracts\HasLabel;

enum LandingPageVariantMatchType: string implements HasLabel
{
    case UtmContent = 'utm_content';
    case UtmTerm = 'utm_term';
    case Primary = 'primary';
    case FirstAvailable = 'first_available';

    public function getLabel(): string
    {
        return __('capell-campaign-studio::generic.landing_page_variant_match_types.' . $this->value);
    }
}
