<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\ExperimentAudienceRules\Pages;

use Capell\Experiments\Filament\Resources\ExperimentAudienceRules\ExperimentAudienceRuleResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateExperimentAudienceRule extends CreateRecord
{
    protected static string $resource = ExperimentAudienceRuleResource::class;
}
