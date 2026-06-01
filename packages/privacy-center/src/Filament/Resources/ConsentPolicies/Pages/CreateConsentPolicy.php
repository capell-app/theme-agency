<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\ConsentPolicies\Pages;

use Capell\PrivacyCenter\Filament\Resources\ConsentPolicies\ConsentPolicyResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateConsentPolicy extends CreateRecord
{
    protected static string $resource = ConsentPolicyResource::class;
}
