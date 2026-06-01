<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Enums;

use Capell\CustomerPortal\Filament\Resources\PortalSupportRequests\PortalSupportRequestResource;

enum ResourceEnum: string
{
    case PortalSupportRequest = PortalSupportRequestResource::class;
}
