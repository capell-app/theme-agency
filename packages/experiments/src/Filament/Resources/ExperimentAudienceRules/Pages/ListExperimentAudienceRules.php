<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\ExperimentAudienceRules\Pages;

use Capell\Experiments\Filament\Resources\ExperimentAudienceRules\ExperimentAudienceRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListExperimentAudienceRules extends ListRecords
{
    protected static string $resource = ExperimentAudienceRuleResource::class;

    /**
     * @return array<int, CreateAction>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
