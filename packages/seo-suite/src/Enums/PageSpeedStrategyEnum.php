<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Enums;

use Filament\Support\Contracts\HasLabel;

enum PageSpeedStrategyEnum: string implements HasLabel
{
    case Mobile = 'mobile';
    case Desktop = 'desktop';

    public function getLabel(): string
    {
        return __('capell-seo-suite::generic.pagespeed_strategy_' . $this->value);
    }
}
