<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\PolicyAcceptances\Pages;

use Capell\PrivacyCenter\Filament\Resources\PolicyAcceptances\PolicyAcceptanceResource;
use Filament\Resources\Pages\ListRecords;

final class ListPolicyAcceptances extends ListRecords
{
    protected static string $resource = PolicyAcceptanceResource::class;
}
