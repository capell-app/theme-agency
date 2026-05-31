<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Data;

use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

final class AutomationTriggerEventData extends Data
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly AutomationTriggerType $triggerType,
        public readonly string $sourceType,
        public readonly ?string $sourceId = null,
        public readonly array $payload = [],
        public readonly ?CarbonImmutable $occurredAt = null,
    ) {}
}
