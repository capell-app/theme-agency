<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\ExperimentVariants\Pages;

use Capell\Experiments\Filament\Resources\ExperimentVariants\ExperimentVariantResource;
use Filament\Resources\Pages\EditRecord;

final class EditExperimentVariant extends EditRecord
{
    protected static string $resource = ExperimentVariantResource::class;
}
