<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Contracts\BookingsAiAdvisor;
use Capell\Bookings\Models\BookingGroupSession;
use Capell\Bookings\Models\BookingOwnerPrompt;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingOwnerPrompt run(BookingGroupSession $groupSession)
 */
class DraftFacebookPostCopyAction
{
    use AsAction;

    public function handle(BookingGroupSession $groupSession): BookingOwnerPrompt
    {
        $advice = app(BookingsAiAdvisor::class)->advise('facebook_post', [
            'booking_group_session_id' => $groupSession->getKey(),
            'title' => $groupSession->title,
            'starts_at' => $groupSession->starts_at->toIso8601String(),
        ]);
        $body = $advice['body'] ?? null;

        return ProposeFacebookPostAction::run($groupSession, is_string($body) ? $body : null);
    }
}
