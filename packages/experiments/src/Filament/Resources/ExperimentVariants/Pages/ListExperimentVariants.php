<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\ExperimentVariants\Pages;

use Capell\Experiments\Filament\Resources\ExperimentVariants\ExperimentVariantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListExperimentVariants extends ListRecords
{
    protected static string $resource = ExperimentVariantResource::class;

    /**
     * @return array<int, CreateAction>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
