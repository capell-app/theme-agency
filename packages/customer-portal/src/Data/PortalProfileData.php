<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Data;

use Capell\CustomerPortal\Enums\PortalAccountStatus;
use Capell\CustomerPortal\Models\PortalAccount;
use Spatie\LaravelData\Data;

class PortalProfileData extends Data
{
    /**
     * @param  array<string, mixed>  $profile
     */
    public function __construct(
        public int $accountId,
        public int $siteId,
        public ?string $email,
        public ?string $displayName,
        public PortalAccountStatus $status,
        public array $profile = [],
    ) {}

    public static function fromAccount(PortalAccount $portalAccount): self
    {
        return new self(
            accountId: (int) $portalAccount->getKey(),
            siteId: (int) $portalAccount->site_id,
            email: $portalAccount->email,
            displayName: $portalAccount->display_name,
            status: $portalAccount->status,
            profile: $portalAccount->profile ?? [],
        );
    }
}
