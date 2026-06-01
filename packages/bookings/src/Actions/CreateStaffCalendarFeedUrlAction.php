<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingStaffMember;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(BookingStaffMember $staffMember)
 */
class CreateStaffCalendarFeedUrlAction
{
    use AsAction;

    public function handle(BookingStaffMember $staffMember): string
    {
        if (! is_string($staffMember->calendar_feed_token) || $staffMember->calendar_feed_token === '') {
            $staffMember->forceFill([
                'calendar_feed_token' => Str::random(64),
            ])->save();
        }

        return route('capell-bookings.calendar.staff', [
            'token' => $staffMember->calendar_feed_token,
        ]);
    }
}
