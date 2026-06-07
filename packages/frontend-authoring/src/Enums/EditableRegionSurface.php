<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Enums;

use Filament\Support\Contracts\HasLabel;

enum EditableRegionSurface: string implements HasLabel
{
    case Field = 'field';
    case LayoutBuilder = 'layout-builder';
    case Media = 'media';

    public function getLabel(): string
    {
        return __('capell-frontend-authoring::authoring.surfaces.' . $this->value);
    }
}
