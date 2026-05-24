<?php

declare(strict_types=1);

namespace Capell\Comments\Enums;

use Filament\Support\Contracts\HasLabel;

enum CommentIdentityMode: string implements HasLabel
{
    case Anonymous = 'anonymous';
    case Authenticated = 'authenticated';
    case Both = 'both';

    public function getLabel(): string
    {
        return (string) __('capell-comments::generic.identity_mode.' . $this->value);
    }
}
