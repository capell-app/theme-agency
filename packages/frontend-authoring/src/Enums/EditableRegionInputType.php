<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Enums;

use Filament\Support\Contracts\HasLabel;

enum EditableRegionInputType: string implements HasLabel
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Html = 'html';

    public function getLabel(): string
    {
        return __('capell-frontend-authoring::authoring.input_types.' . $this->value);
    }

    public function textareaRows(): int
    {
        return $this === self::Html ? 14 : 7;
    }
}
