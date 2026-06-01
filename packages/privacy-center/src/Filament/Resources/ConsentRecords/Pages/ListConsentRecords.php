<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\ConsentRecords\Pages;

use Capell\PrivacyCenter\Filament\Resources\ConsentRecords\ConsentRecordResource;
use Filament\Resources\Pages\ListRecords;

final class ListConsentRecords extends ListRecords
{
    protected static string $resource = ConsentRecordResource::class;
}
