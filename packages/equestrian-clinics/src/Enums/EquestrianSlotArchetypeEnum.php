<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Enums;

use Filament\Support\Contracts\HasLabel;

enum EquestrianSlotArchetypeEnum: string implements HasLabel
{
    case Private = 'private';
    case SemiPrivate = 'semi_private';
    case GroupClinic = 'group_clinic';

    public function getLabel(): string
    {
        return __('capell-equestrian-clinics::package.slot_archetypes.' . $this->value);
    }
}
