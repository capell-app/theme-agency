<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\Experiments\Pages;

use Capell\Experiments\Filament\Resources\Experiments\ExperimentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListExperiments extends ListRecords
{
    protected static string $resource = ExperimentResource::class;

    /**
     * @return array<int, CreateAction>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
