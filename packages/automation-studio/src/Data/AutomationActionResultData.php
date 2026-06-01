<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Data;

use Spatie\LaravelData\Data;

final class AutomationActionResultData extends Data
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public readonly bool $success,
        public readonly ?string $message = null,
        public readonly array $context = [],
    ) {}
}
