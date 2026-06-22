<?php

declare(strict_types=1);

namespace Capell\AiCreator\Data;

use Spatie\LaravelData\Data;

final class AiCreatorStartSessionData extends Data
{
    /**
     * @param  array<string, mixed>  $answers
     */
    public function __construct(
        public readonly string $intent,
        public readonly ?int $siteId = null,
        public readonly ?int $workspaceId = null,
        public readonly ?int $userId = null,
        public readonly array $answers = [],
    ) {}
}
