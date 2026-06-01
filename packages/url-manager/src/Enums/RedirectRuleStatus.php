<?php

declare(strict_types=1);

namespace Capell\UrlManager\Enums;

use Filament\Support\Contracts\HasLabel;

enum RedirectRuleStatus: string implements HasLabel
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => (string) __('capell-url-manager::generic.active'),
            self::Inactive => (string) __('capell-url-manager::generic.inactive'),
        };
    }
}
