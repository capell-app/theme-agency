<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\ConsentPolicies\Pages;

use Capell\PrivacyCenter\Filament\Resources\ConsentPolicies\ConsentPolicyResource;
use Filament\Resources\Pages\EditRecord;

final class EditConsentPolicy extends EditRecord
{
    protected static string $resource = ConsentPolicyResource::class;
}
