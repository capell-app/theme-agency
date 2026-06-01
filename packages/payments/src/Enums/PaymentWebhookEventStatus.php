<?php

declare(strict_types=1);

namespace Capell\Payments\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentWebhookEventStatus: string implements HasLabel
{
    case Received = 'received';
    case Processed = 'processed';
    case Ignored = 'ignored';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return __('capell-payments::generic.webhook_event_statuses.' . $this->value);
    }
}
