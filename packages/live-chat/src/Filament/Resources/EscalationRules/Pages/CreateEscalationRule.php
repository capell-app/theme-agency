<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\EscalationRules\Pages;

use Capell\LiveChat\Filament\Resources\EscalationRules\EscalationRuleResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateEscalationRule extends CreateRecord
{
    protected static string $resource = EscalationRuleResource::class;
}
