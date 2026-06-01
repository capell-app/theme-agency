<?php

declare(strict_types=1);

namespace Capell\Payments\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentRefundStatus: string implements HasLabel
{
    case Pending = 'pending';
    case RequiresAction = 'requires_action';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Canceled = 'canceled';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return __('capell-payments::generic.refund_statuses.' . $this->value);
    }
}
