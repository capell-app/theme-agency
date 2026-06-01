<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\ExperimentGoals\Pages;

use Capell\Experiments\Filament\Resources\ExperimentGoals\ExperimentGoalResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateExperimentGoal extends CreateRecord
{
    protected static string $resource = ExperimentGoalResource::class;
}
