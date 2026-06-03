<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Data\Capabilities;

use Spatie\LaravelData\Data;

final class PageIdCapabilityInputData extends Data
{
    public function __construct(
        public readonly int $page_id,
    ) {}
}
