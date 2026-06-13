<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingChangeProposalPartyStatusEnum;
use Capell\Bookings\Enums\BookingChangeProposalStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingChangeProposal;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingChangeProposal run(AppointmentRequest $appointmentRequest, CarbonImmutable $startsAt, CarbonImmutable $endsAt, ?string $reason = null, ?CarbonImmutable $expiresAt = null)
 */
class ProposeBookingChangeAction
{
    use AsAction;

    public function handle(
        AppointmentRequest $appointmentRequest,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        ?string $reason = null,
        ?CarbonImmutable $expiresAt = null,
    ): BookingChangeProposal {
        return DB::transaction(function () use ($appointmentRequest, $startsAt, $endsAt, $reason, $expiresAt): BookingChangeProposal {
            /** @var BookingChangeProposal $proposal */
            $proposal = BookingChangeProposal::query()->create([
                'appointment_request_id' => $appointmentRequest->getKey(),
                'proposed_starts_at' => $startsAt,
                'proposed_ends_at' => $endsAt,
                'status' => BookingChangeProposalStatusEnum::Pending,
                'reason' => $reason,
                'expires_at' => $expiresAt ?? CarbonImmutable::now()->addDays(2),
            ]);

            foreach (['client', 'staff'] as $party) {
                $token = Str::random(48);

                $proposal->parties()->create([
                    'portal_account_id' => $party === 'client' ? $appointmentRequest->portal_account_id : null,
                    'party' => $party,
                    'status' => BookingChangeProposalPartyStatusEnum::Pending,
                    'token_hash' => hash('sha256', $token),
                    'token_expires_at' => $proposal->expires_at,
                    'meta' => ['issued' => true],
                ]);
            }

            return $proposal->load('parties');
        });
    }
}
