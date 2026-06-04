<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Events;

use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Illuminate\Foundation\Events\Dispatchable;

final class PortalSupportRequestStatusChanged
{
    use Dispatchable;

    public function __construct(
        public readonly PortalSupportRequest $supportRequest,
        public readonly SupportRequestStatus $previousStatus,
        public readonly SupportRequestStatus $status,
    ) {}
}
