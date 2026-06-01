<?php

declare(strict_types=1);

namespace Capell\Experiments\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class ExperimentVariantData extends Data
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $name,
        public ?string $key = null,
        public int $weight = 100,
        public bool $isControl = false,
        public bool $isActive = true,
        public int $sortOrder = 0,
        public array $payload = [],
    ) {}
}
