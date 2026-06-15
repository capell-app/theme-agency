<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingReviewParticipantStatusEnum;
use Capell\Bookings\Models\BookingReviewParticipant;
use Capell\Bookings\Models\BookingReviewRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingReviewParticipant run(BookingReviewRequest $reviewRequest, string $role, ?string $name = null, ?string $email = null, bool $required = true, ?int $portalAccountId = null, array<string, mixed> $meta = [])
 */
class AddReviewParticipantAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $meta
     */
    public function handle(
        BookingReviewRequest $reviewRequest,
        string $role,
        ?string $name = null,
        ?string $email = null,
        bool $required = true,
        ?int $portalAccountId = null,
        array $meta = [],
    ): BookingReviewParticipant {
        $normalizedEmail = is_string($email) && trim($email) !== '' ? strtolower(trim($email)) : null;

        /** @var BookingReviewParticipant $participant */
        $participant = BookingReviewParticipant::query()->updateOrCreate(
            [
                'booking_review_request_id' => $reviewRequest->getKey(),
                'role' => $role,
                'email' => $normalizedEmail,
            ],
            [
                'meta' => $meta,
                'name' => $name,
                'portal_account_id' => $portalAccountId,
                'required' => $required,
                'status' => BookingReviewParticipantStatusEnum::Pending,
            ],
        );

        return $participant;
    }
}
