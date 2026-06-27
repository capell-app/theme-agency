<?php

declare(strict_types=1);

namespace Capell\AccessGate\Enums;

use Filament\Support\Contracts\HasLabel;

enum GrantSubjectType: string implements HasLabel
{
    case Email = 'email';
    case User = 'user';

    public function getLabel(): string
    {
        return __(sprintf('capell-access-gate::filament.grant_subject_type.%s', $this->value));
    }
}
