<?php

declare(strict_types=1);

namespace Capell\Bookings\Contracts;

use Capell\Bookings\Data\BookingMessageData;
use Capell\Bookings\Data\BookingMessageResultData;

interface BookingMessageChannel
{
    public function send(BookingMessageData $messageData): BookingMessageResultData;
}
