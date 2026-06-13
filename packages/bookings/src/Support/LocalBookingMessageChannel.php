<?php

declare(strict_types=1);

namespace Capell\Bookings\Support;

use Capell\Bookings\Contracts\BookingMessageChannel;
use Capell\Bookings\Data\BookingMessageData;
use Capell\Bookings\Data\BookingMessageResultData;
use Illuminate\Support\Str;

final class LocalBookingMessageChannel implements BookingMessageChannel
{
    public function send(BookingMessageData $messageData): BookingMessageResultData
    {
        return new BookingMessageResultData(
            sent: true,
            providerMessageId: 'local-' . Str::uuid()->toString(),
            meta: [
                'channel' => $messageData->channel->value,
                'type' => $messageData->type,
            ],
        );
    }
}
