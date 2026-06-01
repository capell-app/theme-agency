<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\ExperimentGoals\Pages;

use Capell\Experiments\Filament\Resources\ExperimentGoals\ExperimentGoalResource;
use Filament\Resources\Pages\EditRecord;

final class EditExperimentGoal extends EditRecord
{
    protected static string $resource = ExperimentGoalResource::class;
}
