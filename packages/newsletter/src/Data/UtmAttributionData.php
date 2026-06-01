<?php

declare(strict_types=1);

namespace Capell\Newsletter\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class UtmAttributionData extends Data
{
    public function __construct(
        public ?string $source = null,
        public ?string $medium = null,
        public ?string $campaign = null,
        public ?string $term = null,
        public ?string $content = null,
        public ?string $id = null,
    ) {}

    public function hasValues(): bool
    {
        return $this->source !== null
            || $this->medium !== null
            || $this->campaign !== null
            || $this->term !== null
            || $this->content !== null
            || $this->id !== null;
    }

    /**
     * @return array<string, string>
     */
    public function toMetadataArray(): array
    {
        return array_filter(
            $this->toArray(),
            static fn (mixed $value): bool => $value !== null,
        );
    }
}
