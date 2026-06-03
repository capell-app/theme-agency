<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Data\Capabilities;

use Spatie\LaravelData\Data;

final class UpdateDraftPageCapabilityInputData extends Data
{
    /**
     * @param  array<string, mixed>|null  $meta
     * @param  array<string, mixed>|null  $admin
     */
    public function __construct(
        public readonly int $page_id,
        public readonly ?string $name = null,
        public readonly ?array $meta = null,
        public readonly ?array $admin = null,
        public readonly ?string $visible_from = null,
        public readonly ?string $visible_until = null,
    ) {}
}
