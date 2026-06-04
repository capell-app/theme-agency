<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Events;

use Capell\CustomerPortal\Models\PortalSupportRequest;
use Illuminate\Foundation\Events\Dispatchable;

final class PortalSupportRequestSubmitted
{
    use Dispatchable;

    public function __construct(
        public readonly PortalSupportRequest $supportRequest,
    ) {}
}
