<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Models;

use Capell\Core\Models\Site;
use Capell\CustomerPortal\Database\Factories\PortalAccountFactory;
use Capell\CustomerPortal\Enums\PortalAccountStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Override;

/**
 * @property int $site_id
 * @property string|null $owner_type
 * @property int|null $owner_id
 * @property string|null $email
 * @property string|null $email_hash
 * @property string|null $display_name
 * @property array<string, mixed>|null $profile
 * @property array<string, mixed>|null $preferences
 * @property PortalAccountStatus $status
 */
class PortalAccount extends Model
{
    /** @use HasFactory<PortalAccountFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'owner_type',
        'owner_id',
        'email',
        'email_hash',
        'display_name',
        'profile',
        'preferences',
        'status',
        'last_seen_at',
    ];

    public static function normalizeEmail(?string $email): ?string
    {
        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        return Str::lower(trim($email));
    }

    public static function emailHash(?string $email): ?string
    {
        $normalizedEmail = self::normalizeEmail($email);

        return $normalizedEmail === null ? null : self::hashIdentity($normalizedEmail);
    }

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-customer-portal.tables.accounts');

        return is_string($tableName) ? $tableName : 'portal_accounts';
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
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<PortalSupportRequest, $this>
     */
    public function supportRequests(): HasMany
    {
        return $this->hasMany(PortalSupportRequest::class, 'portal_account_id');
    }

    #[Override]
    protected static function booted(): void
    {
        static::saving(function (PortalAccount $portalAccount): void {
            $portalAccount->email = self::normalizeEmail($portalAccount->email);
            $portalAccount->email_hash = self::emailHash($portalAccount->email);
        });
    }

    protected static function newFactory(): PortalAccountFactory
    {
        return PortalAccountFactory::new();
    }

    /**
     * @param  Builder<PortalAccount>  $query
     * @return Builder<PortalAccount>
     */
    protected function scopeForEmail(Builder $query, int $siteId, string $email): Builder
    {
        return $query
            ->where('site_id', $siteId)
            ->where('email_hash', self::emailHash($email));
    }

    /**
     * @param  Builder<PortalAccount>  $query
     * @return Builder<PortalAccount>
     */
    protected function scopeForOwner(Builder $query, int $siteId, string $ownerType, int $ownerId): Builder
    {
        return $query
            ->where('site_id', $siteId)
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'email' => 'encrypted',
            'display_name' => 'encrypted',
            'profile' => 'encrypted:array',
            'preferences' => 'encrypted:array',
            'status' => PortalAccountStatus::class,
            'last_seen_at' => 'immutable_datetime',
        ];
    }

    private static function hashIdentity(string $value): string
    {
        $secret = config('capell-customer-portal.hash_secret') ?: config('app.key');

        return hash_hmac('sha256', $value, (string) $secret);
    }
}
