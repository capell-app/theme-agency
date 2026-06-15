<?php

declare(strict_types=1);

namespace Capell\Bookings\Data;

use Capell\Bookings\Enums\BookingMessageChannelEnum;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class BookingMessageData extends Data
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public BookingMessageChannelEnum $channel,
        public string $recipient,
        public string $type,
        public string $body,
        public ?string $subject = null,
        public ?CarbonImmutable $sendAt = null,
        public array $context = [],
    ) {}
}
