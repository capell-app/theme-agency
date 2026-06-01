<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Enums;

use Filament\Support\Contracts\HasLabel;

enum PrivacyRequestStatus: string implements HasLabel
{
    case Submitted = 'submitted';
    case Verifying = 'verifying';
    case Processing = 'processing';
    case Fulfilled = 'fulfilled';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return __('capell-privacy-center::privacy.privacy_request_statuses.' . $this->value);
    }
}
