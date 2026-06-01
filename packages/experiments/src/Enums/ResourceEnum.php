<?php

declare(strict_types=1);

namespace Capell\Experiments\Enums;

use Capell\Experiments\Filament\Resources\ExperimentAudienceRules\ExperimentAudienceRuleResource;
use Capell\Experiments\Filament\Resources\ExperimentGoals\ExperimentGoalResource;
use Capell\Experiments\Filament\Resources\Experiments\ExperimentResource;
use Capell\Experiments\Filament\Resources\ExperimentVariants\ExperimentVariantResource;

enum ResourceEnum: string
{
    case Experiment = ExperimentResource::class;
    case ExperimentVariant = ExperimentVariantResource::class;
    case ExperimentGoal = ExperimentGoalResource::class;
    case ExperimentAudienceRule = ExperimentAudienceRuleResource::class;
}
