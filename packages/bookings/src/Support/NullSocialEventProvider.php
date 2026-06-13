<?php

declare(strict_types=1);

namespace Capell\Bookings\Support;

use Capell\Bookings\Contracts\SocialEventProvider;
use Capell\Bookings\Models\BookingGroupSession;

final class NullSocialEventProvider implements SocialEventProvider
{
    /**
     * @return array<string, mixed>
     */
    public function publishGroupSession(BookingGroupSession $groupSession, string $copy): array
    {
        $groupSessionKey = $groupSession->getKey();
        $groupSessionIdentifier = is_scalar($groupSessionKey) ? (string) $groupSessionKey : 'unknown';

        return [
            'provider' => 'null',
            'external_event_id' => 'local-group-' . $groupSessionIdentifier,
            'copy' => $copy,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function syncAttendance(BookingGroupSession $groupSession): array
    {
        return [
            'provider' => 'null',
            'interested_count' => 0,
            'going_count' => 0,
        ];
    }
}
