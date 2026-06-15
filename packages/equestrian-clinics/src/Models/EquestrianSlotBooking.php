<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Capell\EquestrianClinics\Enums\EquestrianPaymentStatusEnum;
use Capell\EquestrianClinics\Enums\EquestrianSlotBookingStatusEnum;
use Capell\Payments\Enums\PaymentProvider;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $tour_day_slot_id
 * @property int $rider_profile_id
 * @property int|null $horse_profile_id
 * @property EquestrianSlotBookingStatusEnum $status
 * @property PaymentProvider|null $payment_provider
 * @property EquestrianPaymentStatusEnum $payment_status
 * @property bool $cash_payment
 * @property int $quoted_total_pence
 * @property CarbonImmutable|null $hold_expires_at
 * @property CarbonImmutable|null $refund_available_until
 * @property CarbonImmutable|null $confirmed_at
 * @property CarbonImmutable|null $cancelled_at
 * @property string|null $notes
 * @property array<string, mixed>|null $meta
 * @property-read EquestrianTourDaySlot $slot
 * @property-read EquestrianRiderProfile $riderProfile
 * @property-read EquestrianHorseProfile|null $horseProfile
 */
final class EquestrianSlotBooking extends Model
{
    protected $table = 'equestrian_slot_bookings';

    protected $guarded = [];

    /**
     * @return BelongsTo<EquestrianTourDaySlot, $this>
     */
    public function slot(): BelongsTo
    {
        return $this->belongsTo(EquestrianTourDaySlot::class, 'tour_day_slot_id');
    }

    /**
     * @return BelongsTo<EquestrianRiderProfile, $this>
     */
    public function riderProfile(): BelongsTo
    {
        return $this->belongsTo(EquestrianRiderProfile::class, 'rider_profile_id');
    }

    /**
     * @return BelongsTo<EquestrianHorseProfile, $this>
     */
    public function horseProfile(): BelongsTo
    {
        return $this->belongsTo(EquestrianHorseProfile::class, 'horse_profile_id');
    }

    public function isActiveHold(CarbonImmutable $now): bool
    {
        return $this->status === EquestrianSlotBookingStatusEnum::Held
            && $this->hold_expires_at instanceof CarbonImmutable
            && $this->hold_expires_at->greaterThan($now);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'cancelled_at' => 'immutable_datetime',
            'cash_payment' => 'boolean',
            'confirmed_at' => 'immutable_datetime',
            'hold_expires_at' => 'immutable_datetime',
            'meta' => 'json',
            'payment_provider' => PaymentProvider::class,
            'payment_status' => EquestrianPaymentStatusEnum::class,
            'quoted_total_pence' => 'integer',
            'refund_available_until' => 'immutable_datetime',
            'status' => EquestrianSlotBookingStatusEnum::class,
        ];
    }
}
