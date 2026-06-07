<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Data;

use Spatie\LaravelData\Data;

final class FrontendResourceHintData extends Data
{
    public function __construct(
        public string $rel,
        public string $href,
        public ?string $as = null,
        public ?string $type = null,
        public ?string $crossorigin = null,
        public ?string $fetchpriority = null,
    ) {}

    /** @return array<string, mixed> */
    public function signature(): array
    {
        return array_filter([
            'as' => $this->as,
            'crossorigin' => $this->crossorigin,
            'fetchpriority' => $this->fetchpriority,
            'href' => $this->href,
            'rel' => $this->rel,
            'type' => $this->type,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
