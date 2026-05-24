<?php

declare(strict_types=1);

namespace Capell\Comments\Data;

use Spatie\LaravelData\Data;

class CommentEmailTemplateData extends Data
{
    /**
     * @param  list<string>  $variables
     */
    public function __construct(
        public string $key,
        public string $name,
        public array $variables,
        public ?string $description = null,
    ) {}
}
