<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianWaitlistStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianSlotBooking;
use Capell\EquestrianClinics\Models\EquestrianSlotWaitlistEntry;
use Capell\Payments\Enums\PaymentProvider;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianSlotBooking run(EquestrianSlotWaitlistEntry $entry, ?PaymentProvider $provider = null, bool $cashPayment = false, ?CarbonImmutable $now = null)
 */
final class ClaimWaitlistOfferAction
{
    use AsAction;

    public function handle(
        EquestrianSlotWaitlistEntry $entry,
        ?PaymentProvider $provider = null,
        bool $cashPayment = false,
        ?CarbonImmutable $now = null,
    ): EquestrianSlotBooking {
        $now ??= CarbonImmutable::now();

        return DB::transaction(function () use ($entry, $provider, $cashPayment, $now): EquestrianSlotBooking {
            $lockedEntry = EquestrianSlotWaitlistEntry::query()
                ->whereKey($entry->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedEntry->offerIsClaimable($now)) {
                throw ValidationException::withMessages([
                    'waitlist_entry_id' => __('capell-equestrian-clinics::validation.waitlist_offer_not_claimable'),
                ]);
            }

            $lockedEntry->loadMissing(['slot', 'riderProfile', 'horseProfile']);

            $booking = RequestSlotBookingAction::run(
                slot: $lockedEntry->slot,
                riderProfile: $lockedEntry->riderProfile,
                horseProfile: $lockedEntry->horseProfile,
                provider: $provider,
                cashPayment: $cashPayment,
                quotedTotalPence: $lockedEntry->quoted_total_pence,
                now: $now,
            );

            $lockedEntry->forceFill([
                'status' => EquestrianWaitlistStatusEnum::Claimed,
                'claimed_at' => $now,
            ])->save();
            $lockedEntry->slot()->update([
                'waitlist_count' => max(0, $lockedEntry->slot->waitlist_count - 1),
            ]);

            return $booking;
        });
    }
}
