<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Contracts\SocialEventProvider;
use Capell\Bookings\Enums\BookingGroupSessionStatusEnum;
use Capell\Bookings\Models\BookingGroupSession;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingGroupSession run(BookingGroupSession $groupSession, string $copy)
 */
class PublishClinicToFacebookAction
{
    use AsAction;

    public function handle(BookingGroupSession $groupSession, string $copy): BookingGroupSession
    {
        $result = app(SocialEventProvider::class)->publishGroupSession($groupSession, $copy);

        $groupSession->forceFill([
            'status' => BookingGroupSessionStatusEnum::Published,
            'external_event_id' => $result['external_event_id'] ?? $groupSession->external_event_id,
            'meta' => [
                ...($groupSession->meta ?? []),
                'social_publish' => $result,
            ],
        ])->save();

        return $groupSession->refresh();
    }
}
