<?php

declare(strict_types=1);

namespace Capell\PublicActions\Data;

use Capell\PublicActions\Enums\PublicActionDispatchStatus;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class PublicActionDispatchResultData extends Data
{
    public function __construct(
        public bool $success,
        public ?int $responseStatus = null,
        public ?string $responseSummary = null,
        public ?string $externalId = null,
        public ?string $errorMessage = null,
        public ?PublicActionDispatchStatus $dispatchStatus = null,
    ) {}
}
