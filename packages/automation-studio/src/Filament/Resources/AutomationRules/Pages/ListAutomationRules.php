<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Filament\Resources\AutomationRules\Pages;

use Capell\AutomationStudio\Filament\Resources\AutomationRules\AutomationRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListAutomationRules extends ListRecords
{
    protected static string $resource = AutomationRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
