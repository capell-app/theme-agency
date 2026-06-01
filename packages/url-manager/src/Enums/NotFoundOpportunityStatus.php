<?php

declare(strict_types=1);

namespace Capell\UrlManager\Enums;

use Filament\Support\Contracts\HasLabel;

enum NotFoundOpportunityStatus: string implements HasLabel
{
    case Open = 'open';
    case Ignored = 'ignored';
    case Converted = 'converted';

    public function getLabel(): string
    {
        return match ($this) {
            self::Open => (string) __('capell-url-manager::generic.open'),
            self::Ignored => (string) __('capell-url-manager::generic.ignored'),
            self::Converted => (string) __('capell-url-manager::generic.converted'),
        };
    }
}
