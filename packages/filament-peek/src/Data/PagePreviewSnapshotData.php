<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Data;

use Spatie\LaravelData\Data;

final class PagePreviewSnapshotData extends Data
{
    /**
     * @param  array<string, mixed>  $formState
     */
    public function __construct(
        public string $token,
        public int|string $userId,
        public int $pageId,
        public array $formState,
        public ?int $workspaceId = null,
        public ?LayoutBuilderPreviewStateData $layoutBuilderState = null,
    ) {}
}
