<?php

declare(strict_types=1);

namespace Capell\Bookings\Tests\Fixtures;

use Capell\Bookings\Contracts\BookingMessageChannel;
use Capell\Bookings\Data\BookingMessageData;
use Capell\Bookings\Data\BookingMessageResultData;

final class RecordingBookingMessageChannel implements BookingMessageChannel
{
    /**
     * @var list<BookingMessageData>
     */
    public array $messages = [];

    public function __construct(
        private BookingMessageResultData $result,
    ) {}

    public function send(BookingMessageData $messageData): BookingMessageResultData
    {
        $this->messages[] = $messageData;

        return $this->result;
    }
}
