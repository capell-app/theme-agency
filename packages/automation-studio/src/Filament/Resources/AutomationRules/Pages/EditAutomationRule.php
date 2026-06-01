<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Filament\Resources\AutomationRules\Pages;

use Capell\AutomationStudio\Filament\Resources\AutomationRules\AutomationRuleResource;
use Filament\Resources\Pages\EditRecord;

final class EditAutomationRule extends EditRecord
{
    protected static string $resource = AutomationRuleResource::class;
}
