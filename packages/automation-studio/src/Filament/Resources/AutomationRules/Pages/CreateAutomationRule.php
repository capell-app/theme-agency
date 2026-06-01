<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Filament\Resources\AutomationRules\Pages;

use Capell\AutomationStudio\Filament\Resources\AutomationRules\AutomationRuleResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateAutomationRule extends CreateRecord
{
    protected static string $resource = AutomationRuleResource::class;
}
