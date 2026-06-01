<?php

declare(strict_types=1);

namespace Capell\UrlManager\Enums;

use Filament\Support\Contracts\HasLabel;

enum RedirectMatchType: string implements HasLabel
{
    case Exact = 'exact';
    case Prefix = 'prefix';
    case Regex = 'regex';

    public function getLabel(): string
    {
        return match ($this) {
            self::Exact => (string) __('capell-url-manager::generic.match_exact'),
            self::Prefix => (string) __('capell-url-manager::generic.match_prefix'),
            self::Regex => (string) __('capell-url-manager::generic.match_regex'),
        };
    }
}
