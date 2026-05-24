<?php

declare(strict_types=1);

namespace Capell\Events\Models;

use Capell\Core\Models\Concerns\HasStatus;
use Capell\Core\Models\Concerns\HasUserstamps;
use Capell\Core\Models\Contracts\Statusable;
use Capell\Core\Models\Contracts\Userstampable;
use Capell\Core\Models\Site;
use Capell\Events\Database\Factories\EventVenueFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property string|null $city
 * @property string|null $country
 * @property string|null $line1
 * @property string|null $line2
 * @property string|null $map_url
 * @property string $name
 * @property string|null $postal_code
 * @property string|null $state
 * @property-read string $full_address
 */
class EventVenue extends Model implements Statusable, Userstampable
{
    /** @use HasFactory<EventVenueFactory> */
    use HasFactory;

    /** @use HasStatus<self> */
    use HasStatus;

    use HasUserstamps;
    use SoftDeletes;

    protected $table = 'event_venues';

    protected $guarded = [];

    protected static string $factory = EventVenueFactory::class;

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'event_venue_id');
    }

    /**
     * @return HasMany<EventOccurrence, $this>
     */
    public function occurrences(): HasMany
    {
        return $this->hasMany(EventOccurrence::class, 'event_venue_id');
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function scopeAvailableToSite(Builder $query, Site $site): Builder
    {
        return $query->where(function (Builder $query) use ($site): void {
            $query->whereNull('site_id')
                ->orWhere('site_id', $site->getKey());
        });
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    protected function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->line1,
            $this->line2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ], fn (?string $part): bool => $part !== null && $part !== '');

        return implode(', ', $parts);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'meta' => 'json',
            'status' => 'boolean',
        ];
    }
}
