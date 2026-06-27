<?php

declare(strict_types=1);

namespace Capell\AccessGate\Enums;

use Filament\Support\Contracts\HasLabel;

enum ApprovalStrategy: string implements HasLabel
{
    case Manual = 'manual';
    case FirstNAutoApprove = 'first_n_auto_approve';
    case InviteOnly = 'invite_only';
    case AutoApprove = 'auto_approve';

    public function getLabel(): string
    {
        return __(sprintf('capell-access-gate::filament.approval_strategy.%s', $this->value));
    }
}
