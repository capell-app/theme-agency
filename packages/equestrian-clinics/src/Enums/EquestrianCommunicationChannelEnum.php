<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Enums;

use Filament\Support\Contracts\HasLabel;

enum EquestrianCommunicationChannelEnum: string implements HasLabel
{
    case Email = 'email';
    case Sms = 'sms';
    case WhatsApp = 'whatsapp';

    public function getLabel(): string
    {
        return __('capell-equestrian-clinics::package.communication_channels.' . $this->value);
    }
}
