<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Enums;

use Filament\Support\Contracts\HasLabel;

enum EquestrianFacilityResourceTypeEnum: string implements HasLabel
{
    case Arena = 'arena';
    case Field = 'field';
    case Paddock = 'paddock';
    case Stable = 'stable';
    case Horsebox = 'horsebox';
    case Hookup = 'hookup';
    case Equipment = 'equipment';

    public function getLabel(): string
    {
        return __('capell-equestrian-clinics::package.facility_resource_types.' . $this->value);
    }
}
