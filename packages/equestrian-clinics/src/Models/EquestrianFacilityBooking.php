<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $facility_resource_id
 * @property int|null $tour_day_slot_id
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property int $quantity
 * @property array<string, mixed>|null $meta
 * @property-read EquestrianFacilityResource $facilityResource
 */
final class EquestrianFacilityBooking extends Model
{
    protected $table = 'equestrian_facility_bookings';

    protected $guarded = [];

    /**
     * @return BelongsTo<EquestrianFacilityResource, $this>
     */
    public function facilityResource(): BelongsTo
    {
        return $this->belongsTo(EquestrianFacilityResource::class, 'facility_resource_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'ends_at' => 'immutable_datetime',
            'meta' => 'json',
            'quantity' => 'integer',
            'starts_at' => 'immutable_datetime',
        ];
    }
}
