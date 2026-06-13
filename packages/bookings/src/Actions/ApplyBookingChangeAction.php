<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingChangeProposalStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingChangeProposal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingChangeProposal run(BookingChangeProposal $proposal)
 */
class ApplyBookingChangeAction
{
    use AsAction;

    public function handle(BookingChangeProposal $proposal): BookingChangeProposal
    {
        return DB::transaction(function () use ($proposal): BookingChangeProposal {
            /** @var BookingChangeProposal $lockedProposal */
            $lockedProposal = BookingChangeProposal::query()
                ->with('appointmentRequest')
                ->whereKey($proposal->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedProposal->status->isOpen()) {
                throw ValidationException::withMessages([
                    'status' => __('capell-bookings::validation.change_proposal_closed'),
                ]);
            }

            $appointmentRequest = $lockedProposal->appointmentRequest;

            if (! $appointmentRequest instanceof AppointmentRequest) {
                throw ValidationException::withMessages([
                    'appointment_request_id' => __('capell-bookings::validation.appointment_not_found'),
                ]);
            }

            $appointmentRequest->forceFill([
                'requested_starts_at' => $lockedProposal->proposed_starts_at,
                'requested_ends_at' => $lockedProposal->proposed_ends_at,
                'is_time_pinned' => true,
            ])->save();

            $lockedProposal->forceFill(['status' => BookingChangeProposalStatusEnum::Accepted])->save();

            return $lockedProposal->refresh();
        });
    }
}
