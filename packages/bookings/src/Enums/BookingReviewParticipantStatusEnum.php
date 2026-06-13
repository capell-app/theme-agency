<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum BookingReviewParticipantStatusEnum: string implements HasLabel
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Completed = 'completed';
    case Expired = 'expired';
    case Suppressed = 'suppressed';

    public function getLabel(): string
    {
        return __('capell-bookings::enum.booking_review_participant_status_' . $this->value);
    }
}
