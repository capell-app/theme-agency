<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Data;

use Capell\CustomerPortal\Enums\PortalAccountStatus;
use Spatie\LaravelData\Data;

class PortalAccountIdentityData extends Data
{
    /**
     * @param  array<string, mixed>  $profile
     * @param  array<string, mixed>  $preferences
     */
    public function __construct(
        public int $siteId,
        public ?string $email = null,
        public ?string $displayName = null,
        public ?string $ownerType = null,
        public ?int $ownerId = null,
        public array $profile = [],
        public array $preferences = [],
        public PortalAccountStatus $status = PortalAccountStatus::Active,
    ) {}
}
