<?php

declare(strict_types=1);

namespace Capell\PublicActions\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class PublicActionSpamProtectionResultData extends Data
{
    public function __construct(
        public bool $passed,
        public ?string $field = null,
        public ?string $message = null,
    ) {}
}
