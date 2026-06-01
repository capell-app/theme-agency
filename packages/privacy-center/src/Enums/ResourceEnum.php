<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Enums;

use Capell\PrivacyCenter\Filament\Resources\ConsentPolicies\ConsentPolicyResource;
use Capell\PrivacyCenter\Filament\Resources\ConsentRecords\ConsentRecordResource;
use Capell\PrivacyCenter\Filament\Resources\PolicyAcceptances\PolicyAcceptanceResource;
use Capell\PrivacyCenter\Filament\Resources\PrivacyRequests\PrivacyRequestResource;
use Capell\PrivacyCenter\Filament\Resources\RetentionRules\RetentionRuleResource;

enum ResourceEnum: string
{
    case ConsentPolicy = ConsentPolicyResource::class;
    case ConsentRecord = ConsentRecordResource::class;
    case PolicyAcceptance = PolicyAcceptanceResource::class;
    case PrivacyRequest = PrivacyRequestResource::class;
    case RetentionRule = RetentionRuleResource::class;
}
