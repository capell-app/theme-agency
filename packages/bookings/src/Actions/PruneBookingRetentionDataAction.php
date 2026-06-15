<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingMessageLog;
use Capell\Bookings\Models\BookingTravelObservation;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<string, int> run(?CarbonImmutable $now = null)
 */
class PruneBookingRetentionDataAction
{
    use AsAction;

    /**
     * @return array<string, int>
     */
    public function handle(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $messageLogRetentionConfig = config('capell-bookings.message_log_retention_days', 730);
        $travelObservationRetentionConfig = config('capell-bookings.travel_observation_retention_days', 365);
        $messageLogRetentionDays = is_numeric($messageLogRetentionConfig) ? (int) $messageLogRetentionConfig : 730;
        $travelObservationRetentionDays = is_numeric($travelObservationRetentionConfig) ? (int) $travelObservationRetentionConfig : 365;

        $messageLogsDeleted = BookingMessageLog::query()
            ->where('created_at', '<', $now->subDays($messageLogRetentionDays))
            ->delete();
        $travelObservationsDeleted = BookingTravelObservation::query()
            ->where('observed_at', '<', $now->subDays($travelObservationRetentionDays))
            ->delete();

        return [
            'message_logs_deleted' => is_int($messageLogsDeleted) ? $messageLogsDeleted : 0,
            'travel_observations_deleted' => is_int($travelObservationsDeleted) ? $travelObservationsDeleted : 0,
        ];
    }
}
