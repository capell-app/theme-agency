<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\ExperimentAudienceRules\Pages;

use Capell\Experiments\Filament\Resources\ExperimentAudienceRules\ExperimentAudienceRuleResource;
use Filament\Resources\Pages\EditRecord;

final class EditExperimentAudienceRule extends EditRecord
{
    protected static string $resource = ExperimentAudienceRuleResource::class;
}
