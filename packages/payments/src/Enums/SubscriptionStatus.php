<?php

declare(strict_types=1);

namespace Capell\Payments\Enums;

use Filament\Support\Contracts\HasLabel;

enum SubscriptionStatus: string implements HasLabel
{
    case Incomplete = 'incomplete';
    case IncompleteExpired = 'incomplete_expired';
    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';
    case Canceled = 'canceled';
    case Unpaid = 'unpaid';
    case Paused = 'paused';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return __('capell-payments::generic.subscription_statuses.' . $this->value);
    }
}
