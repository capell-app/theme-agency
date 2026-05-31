<?php

declare(strict_types=1);

namespace Capell\Experiments\Enums;

use Filament\Support\Contracts\HasLabel;

enum ExperimentStatus: string implements HasLabel
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Paused = 'paused';
    case Ended = 'ended';

    public function getLabel(): string
    {
        return __('capell-experiments::generic.statuses.' . $this->value);
    }
}
