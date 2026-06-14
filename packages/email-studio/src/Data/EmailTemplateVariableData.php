<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Data;

use Spatie\LaravelData\Data;

class EmailTemplateVariableData extends Data
{
    public function __construct(
        public string $name,
        public ?string $label = null,
        public ?string $description = null,
        public mixed $sampleValue = null,
        public bool $required = true,
    ) {}
}
