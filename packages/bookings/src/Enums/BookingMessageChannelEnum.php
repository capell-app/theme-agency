<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum BookingMessageChannelEnum: string implements HasLabel
{
    case Email = 'email';
    case Sms = 'sms';
    case WhatsApp = 'whatsapp';

    public function getLabel(): string
    {
        return __('capell-bookings::enum.booking_message_channel_' . $this->value);
    }
}
