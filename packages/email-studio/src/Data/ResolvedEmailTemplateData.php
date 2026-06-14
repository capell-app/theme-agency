<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Data;

use Capell\EmailStudio\Models\EmailTemplate;
use Capell\EmailStudio\Models\EmailTemplateVariant;
use Spatie\LaravelData\Data;

class ResolvedEmailTemplateData extends Data
{
    /**
     * @param  array<int, array{email: string, name?: string|null}>  $cc
     * @param  array<int, array{email: string, name?: string|null}>  $bcc
     */
    public function __construct(
        public ?EmailTemplate $template,
        public ?EmailTemplateVariant $variant,
        public RenderedEmailData $rendered,
        public array $cc = [],
        public array $bcc = [],
    ) {}
}
