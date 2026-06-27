<?php

declare(strict_types=1);

namespace Capell\AccessGate\Enums;

use Filament\Support\Contracts\HasLabel;

enum AccessAreaStatus: string implements HasLabel
{
    case Active = 'active';
    case Paused = 'paused';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return __(sprintf('capell-access-gate::filament.area_status.%s', $this->value));
    }
}
