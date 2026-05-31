<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Data;

use Capell\CustomerPortal\Enums\PortalDashboardItemPriority;
use Spatie\LaravelData\Data;

class PortalDashboardItemData extends Data
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $key,
        public string $label,
        public ?string $description = null,
        public ?string $url = null,
        public ?int $count = null,
        public PortalDashboardItemPriority $priority = PortalDashboardItemPriority::Normal,
        public array $meta = [],
    ) {}
}
