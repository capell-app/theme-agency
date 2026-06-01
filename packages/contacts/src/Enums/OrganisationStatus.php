<?php

declare(strict_types=1);

namespace Capell\Contacts\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrganisationStatus: string implements HasLabel
{
    case Active = 'active';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return __('capell-contacts::generic.organisation_status.' . $this->value);
    }
}
