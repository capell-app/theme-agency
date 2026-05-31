<?php

declare(strict_types=1);

namespace Capell\Experiments\Data;

use Capell\Experiments\Enums\AllocationStrategy;
use Capell\Experiments\Enums\ExperimentStatus;
use Capell\Experiments\Enums\ExperimentSubjectType;
use DateTimeInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class ExperimentData extends Data
{
    /**
     * @param  list<ExperimentVariantData|array<string, mixed>>  $variants
     * @param  list<ExperimentGoalData|array<string, mixed>>  $goals
     * @param  list<ExperimentAudienceRuleData|array<string, mixed>>  $audienceRules
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $name,
        public ?string $key = null,
        public ?int $siteId = null,
        public ExperimentStatus $status = ExperimentStatus::Draft,
        public ExperimentSubjectType $subjectType = ExperimentSubjectType::Generic,
        public ?string $subjectClass = null,
        public ?int $subjectId = null,
        public AllocationStrategy $allocationStrategy = AllocationStrategy::StickyWeighted,
        public int $trafficPercentage = 100,
        public ?DateTimeInterface $startsAt = null,
        public ?DateTimeInterface $endsAt = null,
        public array $variants = [],
        public array $goals = [],
        public array $audienceRules = [],
        public array $metadata = [],
    ) {}
}
