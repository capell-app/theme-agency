<?php

declare(strict_types=1);

namespace Capell\Contacts\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContactStatus: string implements HasLabel
{
    case Active = 'active';
    case Archived = 'archived';
    case Blocked = 'blocked';

    public function getLabel(): string
    {
        return __('capell-contacts::generic.contact_status.' . $this->value);
    }
}
