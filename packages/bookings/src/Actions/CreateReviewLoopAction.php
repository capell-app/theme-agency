<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingReviewRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingReviewRequest run(BookingReviewRequest $reviewRequest, array<int, array<string, mixed>> $participants = [])
 */
class CreateReviewLoopAction
{
    use AsAction;

    /**
     * @param  array<int, array<string, mixed>>  $participants
     */
    public function handle(BookingReviewRequest $reviewRequest, array $participants = []): BookingReviewRequest
    {
        if ($participants === []) {
            if ($reviewRequest->participants()->exists()) {
                return $reviewRequest->refresh()->load('participants');
            }

            $participants = $this->defaultParticipants($reviewRequest);
        }

        foreach ($participants as $participant) {
            $role = $participant['role'] ?? 'customer';

            AddReviewParticipantAction::run(
                reviewRequest: $reviewRequest,
                role: is_string($role) && trim($role) !== '' ? trim($role) : 'customer',
                name: is_string($participant['name'] ?? null) ? $participant['name'] : null,
                email: is_string($participant['email'] ?? null) ? $participant['email'] : null,
                required: (bool) ($participant['required'] ?? true),
                portalAccountId: is_numeric($participant['portal_account_id'] ?? null) ? (int) $participant['portal_account_id'] : null,
                meta: is_array($participant['meta'] ?? null) ? $participant['meta'] : [],
            );
        }

        return $reviewRequest->refresh()->load('participants');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function defaultParticipants(BookingReviewRequest $reviewRequest): array
    {
        $appointmentRequest = $reviewRequest->appointmentRequest;

        if (! $appointmentRequest instanceof AppointmentRequest) {
            return [];
        }

        return [[
            'email' => $appointmentRequest->customer_email,
            'name' => $appointmentRequest->customer_name,
            'portal_account_id' => $appointmentRequest->portal_account_id,
            'required' => true,
            'role' => 'customer',
        ]];
    }
}
