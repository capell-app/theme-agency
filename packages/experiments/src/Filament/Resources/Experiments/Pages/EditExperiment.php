<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\Experiments\Pages;

use Capell\Experiments\Filament\Resources\Experiments\ExperimentResource;
use Filament\Resources\Pages\EditRecord;

final class EditExperiment extends EditRecord
{
    protected static string $resource = ExperimentResource::class;
}
