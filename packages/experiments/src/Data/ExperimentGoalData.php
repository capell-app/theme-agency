<?php

declare(strict_types=1);

namespace Capell\Experiments\Data;

use Capell\Experiments\Enums\ExperimentGoalType;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class ExperimentGoalData extends Data
{
    public function __construct(
        public string $name,
        public ?string $key = null,
        public ExperimentGoalType $type = ExperimentGoalType::CustomEvent,
        public ?string $target = null,
        public ?string $valueAmount = null,
        public bool $isPrimary = false,
        public bool $isActive = true,
    ) {}
}
