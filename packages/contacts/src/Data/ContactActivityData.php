<?php

declare(strict_types=1);

namespace Capell\Contacts\Data;

use Capell\Contacts\Enums\ContactActivityType;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class ContactActivityData extends Data
{
    /**
     * @param  array<string, mixed>|null  $payload
     */
    public function __construct(
        public ContactActivityType $type,
        public ?string $summary = null,
        public ?array $payload = null,
        public ?CarbonInterface $occurredAt = null,
    ) {}
}
