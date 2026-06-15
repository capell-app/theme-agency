<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Enums;

use Filament\Support\Contracts\HasLabel;

enum EquestrianHorseHealthRecordTypeEnum: string implements HasLabel
{
    case Vet = 'vet';
    case Farrier = 'farrier';
    case Vaccination = 'vaccination';
    case Medication = 'medication';
    case Dental = 'dental';
    case Bodywork = 'bodywork';
    case General = 'general';

    public function getLabel(): string
    {
        return __('capell-equestrian-clinics::package.horse_health_record_types.' . $this->value);
    }
}
