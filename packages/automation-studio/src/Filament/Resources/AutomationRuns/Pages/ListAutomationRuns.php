<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Filament\Resources\AutomationRuns\Pages;

use Capell\AutomationStudio\Filament\Resources\AutomationRuns\AutomationRunResource;
use Filament\Resources\Pages\ListRecords;

final class ListAutomationRuns extends ListRecords
{
    protected static string $resource = AutomationRunResource::class;
}
