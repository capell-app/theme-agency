<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Data;

use Spatie\LaravelData\Data;

final class AgentDeliveryChunkData extends Data
{
    /**
     * @param  list<array<string, string>>  $references
     * @param  list<string>  $dependsOn
     */
    public function __construct(
        public readonly string $id,
        public readonly string $heading,
        public readonly string $sourceUrl,
        public readonly ?string $summary,
        public readonly string $body,
        public readonly int $order,
        public readonly array $references = [],
        public readonly array $dependsOn = [],
    ) {}
}
