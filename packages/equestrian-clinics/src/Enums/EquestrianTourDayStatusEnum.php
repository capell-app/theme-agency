<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Enums;

use Filament\Support\Contracts\HasLabel;

enum EquestrianTourDayStatusEnum: string implements HasLabel
{
    case Draft = 'draft';
    case Published = 'published';
    case Full = 'full';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function getLabel(): string
    {
        return __('capell-equestrian-clinics::package.tour_day_statuses.' . $this->value);
    }
}
