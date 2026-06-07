<?php

declare(strict_types=1);

namespace Capell\Newsletter\Data;

use Spatie\LaravelData\Data;

class PreferenceCenterUpdateData extends Data
{
    /**
     * @param  list<string>  $segmentHandles
     */
    public function __construct(
        public array $segmentHandles = [],
    ) {}
}
