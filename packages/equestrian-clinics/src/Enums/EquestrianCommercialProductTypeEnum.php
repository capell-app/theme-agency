<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Enums;

use Filament\Support\Contracts\HasLabel;

enum EquestrianCommercialProductTypeEnum: string implements HasLabel
{
    case LessonPack = 'lesson_pack';
    case Membership = 'membership';
    case StableCard = 'stable_card';
    case GiftCard = 'gift_card';
    case Service = 'service';
    case AddOn = 'add_on';

    public function getLabel(): string
    {
        return __('capell-equestrian-clinics::package.commercial_product_types.' . $this->value);
    }
}
