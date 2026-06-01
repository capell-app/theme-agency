<?php

declare(strict_types=1);

namespace Capell\Contacts\Filament\Resources\Leads\Pages;

use Capell\Contacts\Filament\Resources\Leads\LeadResource;
use Filament\Resources\Pages\ListRecords;

final class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;
}
