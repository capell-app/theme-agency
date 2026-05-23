<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Data;

use Spatie\LaravelData\Data;

final class LayoutBuilderPreviewStateData extends Data
{
    /**
     * @param  array<string, mixed>  $containers
     * @param  array<string, mixed>  $assets
     * @param  array<string, mixed>|null  $originalAssets
     * @param  array<string, mixed>  $selectedRecords
     */
    public function __construct(
        public int $layoutId,
        public array $containers,
        public array $assets = [],
        public ?array $originalAssets = null,
        public array $selectedRecords = [],
    ) {}
}
