<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Data;

use Capell\CustomerPortal\Enums\PortalSelfServiceItemType;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

final class PortalSelfServiceItemData extends Data
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $key,
        public PortalSelfServiceItemType $type,
        public string $label,
        public ?string $description = null,
        public ?string $url = null,
        public ?string $status = null,
        public ?CarbonInterface $occurredAt = null,
        public array $meta = [],
    ) {}
}
