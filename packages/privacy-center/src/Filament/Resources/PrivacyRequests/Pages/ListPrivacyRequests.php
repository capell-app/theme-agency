<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\PrivacyRequests\Pages;

use Capell\PrivacyCenter\Filament\Resources\PrivacyRequests\PrivacyRequestResource;
use Filament\Resources\Pages\ListRecords;

final class ListPrivacyRequests extends ListRecords
{
    protected static string $resource = PrivacyRequestResource::class;
}
