<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Enums;

use Filament\Support\Contracts\HasLabel;

enum EditableRegionSaveStatus: string implements HasLabel
{
    case Published = 'published';
    case PendingApproval = 'pending_approval';

    public function getLabel(): string
    {
        return __('capell-frontend-authoring::authoring.save_statuses.' . $this->value);
    }
}
