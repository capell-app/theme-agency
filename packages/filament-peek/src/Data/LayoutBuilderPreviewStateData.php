<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Data;

use Spatie\LaravelData\Data;

final class LayoutBuilderPreviewStateData extends Data
{
    /**
     * @param  array<string, mixed>  $containers
     * @param  array<string, mixed>  $assets
     */
    public function __construct(
        public int $layoutId,
        public array $containers,
        public array $assets = [],
        public ?string $signature = null,
    ) {}
}
