<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Enums;

use Filament\Support\Contracts\HasLabel;

enum CookieCategory: string implements HasLabel
{
    case Essential = 'essential';
    case Analytics = 'analytics';
    case Marketing = 'marketing';
    case Preferences = 'preferences';
    case Functional = 'functional';

    public function getLabel(): string
    {
        return __('capell-privacy-center::privacy.cookie_categories.' . $this->value);
    }
}
