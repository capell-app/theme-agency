<?php

declare(strict_types=1);

namespace Capell\Contacts\Enums;

use Capell\Contacts\Filament\Resources\Activities\ContactActivityResource;
use Capell\Contacts\Filament\Resources\Contacts\ContactResource;
use Capell\Contacts\Filament\Resources\Leads\LeadResource;
use Capell\Contacts\Filament\Resources\Organisations\OrganisationResource;

enum ResourceEnum: string
{
    case Contact = ContactResource::class;
    case Organisation = OrganisationResource::class;
    case Lead = LeadResource::class;
    case ContactActivity = ContactActivityResource::class;
}
