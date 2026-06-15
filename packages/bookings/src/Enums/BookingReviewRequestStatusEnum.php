<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum BookingReviewRequestStatusEnum: string implements HasLabel
{
    case Scheduled = 'scheduled';
    case Sent = 'sent';
    case Completed = 'completed';
    case Suppressed = 'suppressed';

    public function getLabel(): string
    {
        return __('capell-bookings::enum.booking_review_request_status_' . $this->value);
    }
}
