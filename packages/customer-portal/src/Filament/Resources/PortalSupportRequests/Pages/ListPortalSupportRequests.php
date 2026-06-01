<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Filament\Resources\PortalSupportRequests\Pages;

use Capell\CustomerPortal\Filament\Resources\PortalSupportRequests\PortalSupportRequestResource;
use Filament\Resources\Pages\ListRecords;

final class ListPortalSupportRequests extends ListRecords
{
    protected static string $resource = PortalSupportRequestResource::class;
}
