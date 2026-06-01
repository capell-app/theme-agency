<?php

declare(strict_types=1);

namespace Capell\Experiments\Enums;

use Filament\Support\Contracts\HasLabel;

enum AudienceRuleType: string implements HasLabel
{
    case Attribute = 'attribute';
    case Path = 'path';
    case Query = 'query';
    case Referrer = 'referrer';
    case Utm = 'utm';
    case Segment = 'segment';

    public function getLabel(): string
    {
        return __('capell-experiments::generic.audience_rule_types.' . $this->value);
    }
}
