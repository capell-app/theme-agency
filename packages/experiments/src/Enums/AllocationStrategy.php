<?php

declare(strict_types=1);

namespace Capell\Experiments\Enums;

use Filament\Support\Contracts\HasLabel;

enum AllocationStrategy: string implements HasLabel
{
    case Weighted = 'weighted';
    case StickyWeighted = 'sticky_weighted';

    public function getLabel(): string
    {
        return __('capell-experiments::generic.allocation_strategies.' . $this->value);
    }
}
