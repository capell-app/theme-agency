<?php

declare(strict_types=1);

namespace Capell\Contacts\Models;

use Capell\Contacts\Enums\OrganisationStatus;
use Capell\Contacts\Support\ContactsOverviewStatsCache;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;
use Override;

/**
 * @property string|null $name
 * @property string|null $name_key
 * @property string|null $domain
 * @property string|null $website
 * @property array<string, mixed>|null $profile
 * @property OrganisationStatus|null $status
 * @property Pivot|null $pivot
 */
class Organisation extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'name',
        'name_key',
        'domain',
        'website',
        'profile',
        'status',
    ];

    public static function nameKey(string $name): string
    {
        return Str::of($name)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->toString();
    }

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-contacts.tables.organisations');

        return is_string($tableName) ? $tableName : 'contact_organisations';
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return BelongsToMany<Contact, $this>
     */
    public function contacts(): BelongsToMany
    {
        $pivotTable = config('capell-contacts.tables.organisation_memberships');

        return $this
            ->belongsToMany(
                Contact::class,
                is_string($pivotTable) ? $pivotTable : 'contact_organisation_memberships',
                'organisation_id',
                'contact_id',
            )
            ->withPivot(['role', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<Lead, $this>
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * @return HasMany<ContactActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ContactActivity::class);
    }

    #[Override]
    protected static function booted(): void
    {
        static::saving(function (Organisation $organisation): void {
            if (is_string($organisation->name) && trim($organisation->name) !== '') {
                $organisation->name_key = self::nameKey($organisation->name);
            }
        });

        static::saved(fn (Organisation $organisation): null => self::flushOverviewStats($organisation));
        static::deleted(fn (Organisation $organisation): null => self::flushOverviewStats($organisation));
    }

    private static function flushOverviewStats(Organisation $organisation): null
    {
        ContactsOverviewStatsCache::flushForSite(is_int($organisation->site_id) ? $organisation->site_id : null);

        return null;
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'profile' => 'encrypted:array',
            'status' => OrganisationStatus::class,
        ];
    }
}
