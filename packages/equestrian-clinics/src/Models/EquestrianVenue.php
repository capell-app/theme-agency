<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property string $name
 * @property string|null $address_line
 * @property string|null $postal_code
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $google_maps_url
 * @property string|null $facility_notes
 * @property string|null $access_notes
 * @property string|null $parking_notes
 * @property string|null $contact_name
 * @property string|null $contact_email
 * @property string|null $contact_phone
 * @property bool $active
 * @property array<string, mixed>|null $settings
 * @property-read Collection<int, EquestrianTourDay> $tourDays
 * @property-read Collection<int, EquestrianFacilityResource> $facilityResources
 */
final class EquestrianVenue extends Model
{
    protected $table = 'equestrian_venues';

    protected $guarded = [];

    /**
     * @return HasMany<EquestrianTourDay, $this>
     */
    public function tourDays(): HasMany
    {
        return $this->hasMany(EquestrianTourDay::class, 'venue_id');
    }

    /**
     * @return HasMany<EquestrianFacilityResource, $this>
     */
    public function facilityResources(): HasMany
    {
        return $this->hasMany(EquestrianFacilityResource::class, 'venue_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'settings' => 'json',
        ];
    }
}
