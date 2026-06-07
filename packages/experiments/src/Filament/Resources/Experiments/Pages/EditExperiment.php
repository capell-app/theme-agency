<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\Experiments\Pages;

use Capell\Experiments\Filament\Resources\Experiments\ExperimentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditExperiment extends EditRecord
{
    protected static string $resource = ExperimentResource::class;

    /**
     * @return array<int, Action>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            ExperimentResource::resultsAction(),
        ];
    }
}
