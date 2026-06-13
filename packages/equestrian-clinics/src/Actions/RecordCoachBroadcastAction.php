<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianCommunicationChannelEnum;
use Capell\EquestrianClinics\Enums\EquestrianSlotBookingStatusEnum;
use Capell\EquestrianClinics\Enums\EquestrianWaitlistStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianCommunicationLog;
use Capell\EquestrianClinics\Models\EquestrianSlotBooking;
use Capell\EquestrianClinics\Models\EquestrianSlotWaitlistEntry;
use Capell\EquestrianClinics\Models\EquestrianTourDay;
use Capell\EquestrianClinics\Models\EquestrianTourDaySlot;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianCommunicationLog run(EquestrianTourDay $tourDay, EquestrianCommunicationChannelEnum $channel, string $message, string $audience = 'confirmed_bookings', ?EquestrianTourDaySlot $slot = null, ?string $subject = null, ?CarbonImmutable $sentAt = null, ?array $meta = null)
 */
final class RecordCoachBroadcastAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function handle(
        EquestrianTourDay $tourDay,
        EquestrianCommunicationChannelEnum $channel,
        string $message,
        string $audience = 'confirmed_bookings',
        ?EquestrianTourDaySlot $slot = null,
        ?string $subject = null,
        ?CarbonImmutable $sentAt = null,
        ?array $meta = null,
    ): EquestrianCommunicationLog {
        $sentAt ??= CarbonImmutable::now();
        $recipients = $this->recipients($tourDay, $audience, $slot);

        return EquestrianCommunicationLog::query()->create([
            'tour_day_id' => $tourDay->getKey(),
            'tour_day_slot_id' => $slot?->getKey(),
            'channel' => $channel,
            'audience' => $audience,
            'subject' => $subject,
            'message' => $message,
            'recipient_count' => $recipients->count(),
            'recipients' => $recipients->values()->all(),
            'sent_at' => $sentAt,
            'meta' => $meta,
        ]);
    }

    /**
     * @return Collection<int, array{name: string, email: string|null, phone: string|null, rider_profile_id: int}>
     */
    private function recipients(EquestrianTourDay $tourDay, string $audience, ?EquestrianTourDaySlot $slot): Collection
    {
        if ($audience === 'waitlist') {
            return $tourDay->slots()
                ->when($slot instanceof EquestrianTourDaySlot, static function (Builder $query) use ($slot): void {
                    $query->whereKey($slot->getKey());
                })
                ->with(['waitlistEntries' => static function (HasMany $query): void {
                    $query->whereIn('status', [
                        EquestrianWaitlistStatusEnum::Waiting,
                        EquestrianWaitlistStatusEnum::Offered,
                    ])->with('riderProfile');
                }])
                ->get()
                ->flatMap(static fn (EquestrianTourDaySlot $tourDaySlot): Collection => $tourDaySlot->waitlistEntries->map(
                    static fn (EquestrianSlotWaitlistEntry $entry): array => [
                        'name' => $entry->riderProfile->name,
                        'email' => $entry->riderProfile->email,
                        'phone' => $entry->riderProfile->emergency_contact_phone,
                        'rider_profile_id' => $entry->rider_profile_id,
                    ],
                ));
        }

        return $tourDay->slots()
            ->when($slot instanceof EquestrianTourDaySlot, static function (Builder $query) use ($slot): void {
                $query->whereKey($slot->getKey());
            })
            ->with(['bookings' => static function (HasMany $query): void {
                $query->where('status', EquestrianSlotBookingStatusEnum::Confirmed)->with('riderProfile');
            }])
            ->get()
            ->flatMap(static fn (EquestrianTourDaySlot $tourDaySlot): Collection => $tourDaySlot->bookings->map(
                static fn (EquestrianSlotBooking $booking): array => [
                    'name' => $booking->riderProfile->name,
                    'email' => $booking->riderProfile->email,
                    'phone' => $booking->riderProfile->emergency_contact_phone,
                    'rider_profile_id' => $booking->rider_profile_id,
                ],
            ));
    }
}
