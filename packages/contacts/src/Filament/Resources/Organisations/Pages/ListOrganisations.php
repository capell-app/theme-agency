<?php

declare(strict_types=1);

namespace Capell\Contacts\Filament\Resources\Organisations\Pages;

use Capell\Contacts\Filament\Resources\Organisations\OrganisationResource;
use Filament\Resources\Pages\ListRecords;

final class ListOrganisations extends ListRecords
{
    protected static string $resource = OrganisationResource::class;
}
