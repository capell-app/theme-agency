<?php

declare(strict_types=1);

namespace Capell\LiveChat\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class LiveChatAvailabilityData extends Data
{
    public function __construct(
        public bool $available,
        public string $timezone,
        public string $message,
        public ?CarbonInterface $checkedAt = null,
    ) {}
}
