<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Data;

use Spatie\LaravelData\Data;

final class AgentDeliveryPageIndexEntryData extends Data
{
    public function __construct(
        public readonly string $canonicalUrl,
        public readonly string $url,
        public readonly string $language,
        public readonly ?string $lastUpdatedAt,
        public readonly string $manifestUrl,
        public readonly string $chunksUrl,
    ) {}
}
