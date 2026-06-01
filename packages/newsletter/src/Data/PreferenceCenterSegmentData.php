<?php

declare(strict_types=1);

namespace Capell\Newsletter\Data;

use Spatie\LaravelData\Data;

class PreferenceCenterSegmentData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $handle,
        public bool $selected,
    ) {}
}
