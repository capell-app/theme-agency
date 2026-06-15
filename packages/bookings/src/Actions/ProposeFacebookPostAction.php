<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingOwnerPromptStatusEnum;
use Capell\Bookings\Models\BookingGroupSession;
use Capell\Bookings\Models\BookingOwnerPrompt;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingOwnerPrompt run(BookingGroupSession $groupSession, ?string $body = null)
 */
class ProposeFacebookPostAction
{
    use AsAction;

    public function handle(BookingGroupSession $groupSession, ?string $body = null): BookingOwnerPrompt
    {
        /** @var BookingOwnerPrompt $prompt */
        $prompt = BookingOwnerPrompt::query()->create([
            'site_id' => $groupSession->site_id,
            'type' => 'facebook_post',
            'status' => BookingOwnerPromptStatusEnum::Proposed,
            'title' => __('capell-bookings::ai.facebook_post_title'),
            'body' => $body ?? __('capell-bookings::ai.facebook_post_body', ['title' => $groupSession->title]),
            'context' => [
                'booking_group_session_id' => $groupSession->getKey(),
                'starts_at' => $groupSession->starts_at->toIso8601String(),
            ],
        ]);

        return $prompt;
    }
}
