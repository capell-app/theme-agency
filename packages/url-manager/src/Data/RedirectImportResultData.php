<?php

declare(strict_types=1);

namespace Capell\UrlManager\Data;

use Spatie\LaravelData\Data;

final class RedirectImportResultData extends Data
{
    /**
     * @param  list<string>  $errors
     */
    public function __construct(
        public readonly int $imported,
        public readonly int $skipped,
        public readonly array $errors = [],
    ) {}
}
