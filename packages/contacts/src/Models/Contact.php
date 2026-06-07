<?php

declare(strict_types=1);

namespace Capell\Contacts\Models;

use Capell\Contacts\Enums\ContactStatus;
use Capell\Contacts\Support\ContactsOverviewStatsCache;
use Capell\Core\Models\Site;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Override;

/**
 * @property int|null $site_id
 * @property string|null $source_type
 * @property int|null $source_id
 * @property string|null $source_key
 * @property string|null $source_identifier
 * @property string|null $source_identifier_hash
 * @property string|null $email
 * @property string|null $email_hash
 * @property string|null $phone
 * @property string|null $phone_hash
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $display_name
 * @property array<string, mixed>|null $profile
 * @property ContactStatus|null $status
 * @property CarbonImmutable|null $first_seen_at
 * @property CarbonImmutable|null $last_seen_at
 */
class Contact extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'source_type',
        'source_id',
        'source_key',
        'source_identifier',
        'source_identifier_hash',
        'email',
        'email_hash',
        'phone',
        'phone_hash',
        'first_name',
        'last_name',
        'display_name',
        'profile',
        'status',
        'first_seen_at',
        'last_seen_at',
    ];

    public static function emailHash(?string $email): ?string
    {
        $normalizedEmail = self::normalizeEmail($email);

        return $normalizedEmail === null ? null : self::hashIdentity($normalizedEmail);
    }

    public static function phoneHash(?string $phone): ?string
    {
        $normalizedPhone = self::normalizePhone($phone);

        return $normalizedPhone === null ? null : self::hashIdentity($normalizedPhone);
    }

    public static function sourceIdentifierHash(?string $sourceIdentifier): ?string
    {
        $normalizedIdentifier = self::normalizeSourceIdentifier($sourceIdentifier);

        return $normalizedIdentifier === null ? null : self::hashIdentity($normalizedIdentifier);
    }

    public static function normalizeEmail(?string $email): ?string
    {
        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        return Str::lower(trim($email));
    }

    public static function normalizePhone(?string $phone): ?string
    {
        if (! is_string($phone) || trim($phone) === '') {
            return null;
        }

        $normalizedPhone = preg_replace('/[^0-9+]/', '', trim($phone));

        return is_string($normalizedPhone) && $normalizedPhone !== '' ? $normalizedPhone : null;
    }

    public static function normalizeSourceIdentifier(?string $sourceIdentifier): ?string
    {
        if (! is_string($sourceIdentifier) || trim($sourceIdentifier) === '') {
            return null;
        }

        return Str::lower(trim($sourceIdentifier));
    }

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-contacts.tables.contacts');

        return is_string($tableName) ? $tableName : 'contacts';
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsToMany<Organisation, $this>
     */
    public function organisations(): BelongsToMany
    {
        $pivotTable = config('capell-contacts.tables.organisation_memberships');

        return $this
            ->belongsToMany(
                Organisation::class,
                is_string($pivotTable) ? $pivotTable : 'contact_organisation_memberships',
                'contact_id',
                'organisation_id',
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

    /**
     * @return BelongsToMany<ContactTag, $this>
     */
    public function tags(): BelongsToMany
    {
        $pivotTable = config('capell-contacts.tables.contact_tag_memberships');

        return $this->belongsToMany(
            ContactTag::class,
            is_string($pivotTable) ? $pivotTable : 'contact_tag_memberships',
            'contact_id',
            'contact_tag_id',
        )->withTimestamps();
    }

    /**
     * @param  Builder<Contact>  $query
     * @return Builder<Contact>
     */
    public function scopeWithTag(Builder $query, string $tag): Builder
    {
        $slug = ContactTag::slugFor($tag);

        if ($slug === '') {
            return $query;
        }

        return $query->whereHas(
            'tags',
            static fn (Builder $tagQuery): Builder => $tagQuery->where('slug', $slug),
        );
    }

    #[Override]
    protected static function booted(): void
    {
        static::saving(function (Contact $contact): void {
            $contact->email_hash = self::emailHash($contact->email);
            $contact->phone_hash = self::phoneHash($contact->phone);
            $contact->source_identifier_hash = self::sourceIdentifierHash($contact->source_identifier);
        });

        static::saved(fn (Contact $contact): null => self::flushOverviewStats($contact));
        static::deleted(fn (Contact $contact): null => self::flushOverviewStats($contact));
    }

    /**
     * @param  Builder<Contact>  $query
     * @return Builder<Contact>
     */
    protected function scopeForEmail(Builder $query, int $siteId, string $email): Builder
    {
        return $query
            ->where('site_id', $siteId)
            ->where('email_hash', self::emailHash($email));
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'email' => 'encrypted',
            'phone' => 'encrypted',
            'source_identifier' => 'encrypted',
            'first_name' => 'encrypted',
            'last_name' => 'encrypted',
            'display_name' => 'encrypted',
            'profile' => 'encrypted:array',
            'status' => ContactStatus::class,
            'first_seen_at' => 'immutable_datetime',
            'last_seen_at' => 'immutable_datetime',
        ];
    }

    private static function flushOverviewStats(Contact $contact): null
    {
        ContactsOverviewStatsCache::flushForSite($contact->site_id);

        return null;
    }

    private static function hashIdentity(string $value): string
    {
        $secret = config('capell-contacts.hash_secret') ?: config('app.key');

        return hash_hmac('sha256', $value, (string) $secret);
    }
}
