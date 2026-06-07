<?php

declare(strict_types=1);

namespace Capell\Newsletter\Enums;

use Filament\Support\Contracts\HasLabel;

enum SubscriberStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';
    case Suppressed = 'suppressed';
    case Bounced = 'bounced';
    case Complained = 'complained';

    public function getLabel(): string
    {
        return __('capell-newsletter::generic.subscriber_status.' . $this->value);
    }

    public function isSendable(): bool
    {
        return $this === self::Subscribed;
    }

    public function isGloballySuppressed(): bool
    {
        return in_array($this, [
            self::Suppressed,
            self::Bounced,
            self::Complained,
        ], true);
    }
}
