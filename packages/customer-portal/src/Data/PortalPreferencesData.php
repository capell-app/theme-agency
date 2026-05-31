<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Data;

use Spatie\LaravelData\Data;

class PortalPreferencesData extends Data
{
    /**
     * @param  array<string, mixed>  $values
     */
    public function __construct(
        public array $values = [],
        public bool $replace = false,
    ) {}
}
