<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingGroupSession;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(BookingGroupSession $groupSession)
 */
class AssignGroupSlotTimesAction
{
    use AsAction;

    public function handle(BookingGroupSession $groupSession): int
    {
        /** @var EloquentCollection<int, AppointmentRequest> $appointmentRequests */
        $appointmentRequests = $groupSession->appointmentRequests()
            ->orderBy('created_at')
            ->get();

        if ($appointmentRequests->isEmpty()) {
            return 0;
        }

        $durationMinutes = max(1, (int) floor($groupSession->starts_at->diffInMinutes($groupSession->ends_at) / $appointmentRequests->count()));
        $cursor = $groupSession->starts_at;

        foreach ($appointmentRequests as $appointmentRequest) {
            $appointmentRequest->forceFill([
                'requested_starts_at' => $cursor,
                'requested_ends_at' => $cursor->addMinutes($durationMinutes),
                'is_time_pinned' => true,
            ])->save();

            $cursor = $cursor->addMinutes($durationMinutes);
        }

        return $appointmentRequests->count();
    }
}
