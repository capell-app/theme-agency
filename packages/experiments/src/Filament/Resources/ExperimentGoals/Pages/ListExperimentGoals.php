<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\ExperimentGoals\Pages;

use Capell\Experiments\Filament\Resources\ExperimentGoals\ExperimentGoalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListExperimentGoals extends ListRecords
{
    protected static string $resource = ExperimentGoalResource::class;

    /**
     * @return array<int, CreateAction>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
