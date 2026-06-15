<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Capell\EquestrianClinics\Enums\EquestrianFacilityResourceTypeEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int $venue_id
 * @property string $name
 * @property EquestrianFacilityResourceTypeEnum $type
 * @property int $capacity
 * @property int $price_pence
 * @property bool $active
 * @property array<string, mixed>|null $settings
 * @property-read EquestrianVenue $venue
 * @property-read Collection<int, EquestrianFacilityBooking> $bookings
 */
final class EquestrianFacilityResource extends Model
{
    protected $table = 'equestrian_facility_resources';

    protected $guarded = [];

    /**
     * @return BelongsTo<EquestrianVenue, $this>
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(EquestrianVenue::class, 'venue_id');
    }

    /**
     * @return HasMany<EquestrianFacilityBooking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(EquestrianFacilityBooking::class, 'facility_resource_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'capacity' => 'integer',
            'price_pence' => 'integer',
            'settings' => 'json',
            'type' => EquestrianFacilityResourceTypeEnum::class,
        ];
    }
}
