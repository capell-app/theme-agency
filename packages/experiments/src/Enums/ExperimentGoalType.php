<?php

declare(strict_types=1);

namespace Capell\Experiments\Enums;

use Filament\Support\Contracts\HasLabel;

enum ExperimentGoalType: string implements HasLabel
{
    case PageView = 'page_view';
    case Click = 'click';
    case FormSubmission = 'form_submission';
    case CustomEvent = 'custom_event';

    public function getLabel(): string
    {
        return __('capell-experiments::generic.goal_types.' . $this->value);
    }
}
