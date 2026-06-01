<?php

declare(strict_types=1);

namespace Capell\Experiments\Enums;

use Filament\Support\Contracts\HasLabel;

enum ExperimentSubjectType: string implements HasLabel
{
    case Page = 'page';
    case Campaign = 'campaign';
    case Generic = 'generic';

    public function getLabel(): string
    {
        return __('capell-experiments::generic.subject_types.' . $this->value);
    }
}
