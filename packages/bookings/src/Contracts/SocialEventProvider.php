<?php

declare(strict_types=1);

namespace Capell\Bookings\Contracts;

use Capell\Bookings\Models\BookingGroupSession;

interface SocialEventProvider
{
    /**
     * @return array<string, mixed>
     */
    public function publishGroupSession(BookingGroupSession $groupSession, string $copy): array;

    /**
     * @return array<string, mixed>
     */
    public function syncAttendance(BookingGroupSession $groupSession): array;
}
