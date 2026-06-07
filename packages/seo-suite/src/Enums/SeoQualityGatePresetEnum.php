<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Enums;

use Filament\Support\Contracts\HasLabel;

enum SeoQualityGatePresetEnum: string implements HasLabel
{
    case Relaxed = 'relaxed';
    case Standard = 'standard';
    case Strict = 'strict';
    case Agency = 'agency';

    public function getLabel(): string
    {
        return __('capell-seo-suite::generic.seo_quality_gate_preset_' . $this->value);
    }
}
