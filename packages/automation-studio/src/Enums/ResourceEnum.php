<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Enums;

use Capell\AutomationStudio\Filament\Resources\AutomationRules\AutomationRuleResource;
use Capell\AutomationStudio\Filament\Resources\AutomationRuns\AutomationRunResource;

enum ResourceEnum: string
{
    case AutomationRule = AutomationRuleResource::class;
    case AutomationRun = AutomationRunResource::class;
}
