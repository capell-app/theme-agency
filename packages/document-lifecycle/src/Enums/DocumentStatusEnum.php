<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DocumentStatusEnum: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Active => 'success',
            self::Archived => 'warning',
        };
    }

    public function getLabel(): string
    {
        return __('capell-document-lifecycle::navigation.status.' . $this->value);
    }
}
