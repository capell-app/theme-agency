<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Data\Capabilities;

use Spatie\LaravelData\Data;

final class CreateDraftPageCapabilityInputData extends Data
{
    /**
     * @param  array<string, mixed>|null  $meta
     * @param  array<string, mixed>|null  $admin
     */
    public function __construct(
        public readonly string $name,
        public readonly int $site_id,
        public readonly int $blueprint_id,
        public readonly int $layout_id,
        public readonly ?int $parent_id = null,
        public readonly ?array $meta = null,
        public readonly ?array $admin = null,
        public readonly ?string $visible_from = null,
        public readonly ?string $visible_until = null,
    ) {}
}
