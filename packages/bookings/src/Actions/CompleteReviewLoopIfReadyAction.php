<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingReviewParticipantStatusEnum;
use Capell\Bookings\Enums\BookingReviewRequestStatusEnum;
use Capell\Bookings\Models\BookingReviewParticipant;
use Capell\Bookings\Models\BookingReviewRequest;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingReviewRequest run(BookingReviewRequest $reviewRequest)
 */
class CompleteReviewLoopIfReadyAction
{
    use AsAction;

    public function handle(BookingReviewRequest $reviewRequest): BookingReviewRequest
    {
        /** @var EloquentCollection<int, BookingReviewParticipant> $participants */
        $participants = $reviewRequest->participants()->get();

        if ($participants->isEmpty()) {
            return $reviewRequest;
        }

        $requiredParticipants = $participants->filter(static fn (BookingReviewParticipant $participant): bool => $participant->required);
        $blockingParticipants = $requiredParticipants->isNotEmpty() ? $requiredParticipants : $participants;
        $hasPendingRequiredParticipant = $blockingParticipants
            ->contains(static fn (BookingReviewParticipant $participant): bool => $participant->status !== BookingReviewParticipantStatusEnum::Completed);

        if ($hasPendingRequiredParticipant) {
            return $reviewRequest->refresh();
        }

        $completedParticipants = $participants
            ->filter(static fn (BookingReviewParticipant $participant): bool => $participant->status === BookingReviewParticipantStatusEnum::Completed);
        $ratings = $completedParticipants
            ->pluck('rating')
            ->filter(static fn (mixed $rating): bool => is_int($rating))
            ->values();
        $averageRating = $ratings->isNotEmpty() ? round((float) $ratings->avg(), 2) : null;
        $responses = $completedParticipants
            ->filter(static fn (BookingReviewParticipant $participant): bool => is_string($participant->response) && trim($participant->response) !== '')
            ->map(static fn (BookingReviewParticipant $participant): string => sprintf('%s: %s', $participant->role, trim((string) $participant->response)))
            ->values()
            ->all();

        $reviewRequest->forceFill([
            'completed_at' => CarbonImmutable::now(),
            'meta' => array_merge($reviewRequest->meta ?? [], [
                'average_rating' => $averageRating,
                'completed_participants' => $completedParticipants->count(),
                'required_participants' => $requiredParticipants->count(),
                'total_participants' => $participants->count(),
            ]),
            'rating' => $averageRating !== null ? (int) round($averageRating) : null,
            'response' => $responses !== [] ? implode("\n\n", $responses) : null,
            'status' => BookingReviewRequestStatusEnum::Completed,
        ])->save();

        return $reviewRequest->refresh();
    }
}
