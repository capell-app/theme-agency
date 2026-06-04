<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Data;

use Spatie\LaravelData\Data;

final class PortalPreferenceOptionData extends Data
{
    public function __construct(
        public string $key,
        public string $label,
    ) {}
}
