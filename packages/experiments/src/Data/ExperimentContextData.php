<?php

declare(strict_types=1);

namespace Capell\Experiments\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class ExperimentContextData extends Data
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>  $utm
     * @param  list<string>  $segments
     */
    public function __construct(
        public ?int $siteId = null,
        public ?string $subjectType = null,
        public ?int $subjectId = null,
        public ?string $source = null,
        public ?string $externalId = null,
        public ?string $path = null,
        public ?string $url = null,
        public ?string $referrer = null,
        public array $attributes = [],
        public array $query = [],
        public array $utm = [],
        public array $segments = [],
    ) {}
}
