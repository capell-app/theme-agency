<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum BookingLessonBundleStatusEnum: string implements HasLabel
{
    case Active = 'active';
    case Exhausted = 'exhausted';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return __('capell-bookings::enum.booking_lesson_bundle_status.' . $this->value);
    }
}
