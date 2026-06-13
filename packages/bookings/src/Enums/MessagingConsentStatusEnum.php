<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum MessagingConsentStatusEnum: string implements HasLabel
{
    case Granted = 'granted';
    case Revoked = 'revoked';

    public function allowsMessages(): bool
    {
        return $this === self::Granted;
    }

    public function getLabel(): string
    {
        return __('capell-bookings::enum.messaging_consent_status_' . $this->value);
    }
}
