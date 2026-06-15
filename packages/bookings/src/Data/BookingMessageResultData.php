<?php

declare(strict_types=1);

namespace Capell\Bookings\Data;

use Spatie\LaravelData\Data;

class BookingMessageResultData extends Data
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public bool $sent,
        public ?string $providerMessageId = null,
        public ?string $error = null,
        public array $meta = [],
    ) {}
}
