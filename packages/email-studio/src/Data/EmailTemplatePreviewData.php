<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Data;

use Spatie\LaravelData\Data;

class EmailTemplatePreviewData extends Data
{
    /**
     * @param  array<string, mixed>  $sampleData
     * @param  list<string>  $missingVariables
     */
    public function __construct(
        public ?RenderedEmailData $rendered,
        public array $sampleData = [],
        public array $missingVariables = [],
        public ?string $error = null,
    ) {}
}
