<?php

declare(strict_types=1);

namespace Capell\Experiments\Data;

use Capell\Experiments\Enums\AudienceOperator;
use Capell\Experiments\Enums\AudienceRuleType;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class ExperimentAudienceRuleData extends Data
{
    public function __construct(
        public AudienceRuleType $type,
        public string $key,
        public AudienceOperator $operator,
        public mixed $value = null,
        public bool $isRequired = true,
        public bool $isActive = true,
        public int $sortOrder = 0,
    ) {}
}
