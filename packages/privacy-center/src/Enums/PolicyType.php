<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Enums;

use Filament\Support\Contracts\HasLabel;

enum PolicyType: string implements HasLabel
{
    case Privacy = 'privacy';
    case Cookie = 'cookie';
    case Terms = 'terms';
    case DataProcessing = 'data_processing';

    public function getLabel(): string
    {
        return __('capell-privacy-center::privacy.policy_types.' . $this->value);
    }
}
