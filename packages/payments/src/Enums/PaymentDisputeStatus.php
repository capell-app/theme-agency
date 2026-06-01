<?php

declare(strict_types=1);

namespace Capell\Payments\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentDisputeStatus: string implements HasLabel
{
    case WarningNeedsResponse = 'warning_needs_response';
    case WarningUnderReview = 'warning_under_review';
    case WarningClosed = 'warning_closed';
    case NeedsResponse = 'needs_response';
    case UnderReview = 'under_review';
    case Won = 'won';
    case Lost = 'lost';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return __('capell-payments::generic.dispute_statuses.' . $this->value);
    }
}
