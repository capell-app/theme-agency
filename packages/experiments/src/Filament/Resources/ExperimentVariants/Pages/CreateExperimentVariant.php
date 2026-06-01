<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\ExperimentVariants\Pages;

use Capell\Experiments\Filament\Resources\ExperimentVariants\ExperimentVariantResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateExperimentVariant extends CreateRecord
{
    protected static string $resource = ExperimentVariantResource::class;
}
