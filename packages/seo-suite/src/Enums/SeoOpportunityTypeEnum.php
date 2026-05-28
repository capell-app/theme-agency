<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Enums;

use Filament\Support\Contracts\HasLabel;

enum SeoOpportunityTypeEnum: string implements HasLabel
{
    case QuickWin = 'quick_win';
    case CtrOpportunity = 'ctr_opportunity';
    case Declining = 'declining';
    case MissingTargetVisibility = 'missing_target_visibility';
    case Cannibalization = 'cannibalization';
    case TechnicalDrift = 'technical_drift';

    public function getLabel(): string
    {
        return match ($this) {
            self::QuickWin => __('capell-seo-suite::generic.seo_opportunity_quick_win'),
            self::CtrOpportunity => __('capell-seo-suite::generic.seo_opportunity_ctr'),
            self::Declining => __('capell-seo-suite::generic.seo_opportunity_declining'),
            self::MissingTargetVisibility => __('capell-seo-suite::generic.seo_opportunity_missing_target'),
            self::Cannibalization => __('capell-seo-suite::generic.seo_opportunity_cannibalization'),
            self::TechnicalDrift => __('capell-seo-suite::generic.seo_opportunity_technical_drift'),
        };
    }
}
