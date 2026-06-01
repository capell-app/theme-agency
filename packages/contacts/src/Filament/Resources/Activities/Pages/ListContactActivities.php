<?php

declare(strict_types=1);

namespace Capell\Contacts\Filament\Resources\Activities\Pages;

use Capell\Contacts\Filament\Resources\Activities\ContactActivityResource;
use Filament\Resources\Pages\ListRecords;

final class ListContactActivities extends ListRecords
{
    protected static string $resource = ContactActivityResource::class;
}
