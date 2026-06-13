<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Contracts\SocialEventProvider;
use Capell\Bookings\Models\BookingGroupSession;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingGroupSession run(BookingGroupSession $groupSession)
 */
class SyncFacebookAttendanceAction
{
    use AsAction;

    public function handle(BookingGroupSession $groupSession): BookingGroupSession
    {
        $attendance = app(SocialEventProvider::class)->syncAttendance($groupSession);

        $groupSession->forceFill([
            'social_attendance' => $attendance,
        ])->save();

        return $groupSession->refresh();
    }
}
