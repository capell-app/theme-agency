<?php

declare(strict_types=1);

namespace Capell\Experiments\Data;

use DateTimeInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class ExperimentGoalEventData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public ?string $eventKey = null,
        public ?string $valueAmount = null,
        public ?DateTimeInterface $occurredAt = null,
        public array $metadata = [],
    ) {}
}
