<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Enums;

use Filament\Support\Contracts\HasLabel;

enum RetentionAction: string implements HasLabel
{
    case Delete = 'delete';
    case Anonymize = 'anonymize';
    case Review = 'review';

    public function getLabel(): string
    {
        return __('capell-privacy-center::privacy.retention_actions.' . $this->value);
    }
}
