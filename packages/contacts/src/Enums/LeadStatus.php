<?php

declare(strict_types=1);

namespace Capell\Contacts\Enums;

use Filament\Support\Contracts\HasLabel;

enum LeadStatus: string implements HasLabel
{
    case New = 'new';
    case Open = 'open';
    case Qualified = 'qualified';
    case Won = 'won';
    case Lost = 'lost';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return __('capell-contacts::generic.lead_status.' . $this->value);
    }
}
