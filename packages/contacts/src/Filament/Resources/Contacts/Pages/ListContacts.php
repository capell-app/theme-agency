<?php

declare(strict_types=1);

namespace Capell\Contacts\Filament\Resources\Contacts\Pages;

use Capell\Contacts\Filament\Resources\Contacts\ContactResource;
use Filament\Resources\Pages\ListRecords;

final class ListContacts extends ListRecords
{
    protected static string $resource = ContactResource::class;
}
