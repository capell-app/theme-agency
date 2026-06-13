<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Enums;

use Filament\Support\Contracts\HasLabel;

enum EquestrianCareTaskTypeEnum: string implements HasLabel
{
    case Feed = 'feed';
    case Medication = 'medication';
    case Vet = 'vet';
    case Farrier = 'farrier';
    case Exercise = 'exercise';
    case Vaccination = 'vaccination';
    case Grooming = 'grooming';
    case General = 'general';

    public function getLabel(): string
    {
        return __('capell-equestrian-clinics::package.care_task_types.' . $this->value);
    }
}
