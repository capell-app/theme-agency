<?php

declare(strict_types=1);

namespace Capell\Experiments\Enums;

use Filament\Support\Contracts\HasLabel;

enum AudienceOperator: string implements HasLabel
{
    case Equals = 'equals';
    case NotEquals = 'not_equals';
    case Contains = 'contains';
    case StartsWith = 'starts_with';
    case EndsWith = 'ends_with';
    case In = 'in';
    case NotIn = 'not_in';
    case Exists = 'exists';
    case Missing = 'missing';

    public function getLabel(): string
    {
        return __('capell-experiments::generic.audience_operators.' . $this->value);
    }
}
