<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\Experiments\Pages;

use Capell\Experiments\Filament\Resources\Experiments\ExperimentResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateExperiment extends CreateRecord
{
    protected static string $resource = ExperimentResource::class;
}
