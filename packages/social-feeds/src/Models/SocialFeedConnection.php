<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Models;

use Capell\Core\Models\Site;
use Capell\SocialFeeds\Enums\SocialFeedConnectionStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property string $provider
 * @property string $name
 * @property SocialFeedConnectionStatus $status
 * @property array<string, mixed>|null $credentials
 * @property array<string, mixed>|null $meta
 * @property CarbonImmutable|null $last_synced_at
 * @property string|null $sync_status
 * @property string|null $last_sync_error
 */
final class SocialFeedConnection extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    protected $table = 'social_feed_connections';

    protected $guarded = [];

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * @return HasMany<SocialFeedItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SocialFeedItem::class, 'connection_id');
    }

    public function isConnected(): bool
    {
        return $this->status === SocialFeedConnectionStatus::Connected;
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'meta' => 'array',
            'status' => SocialFeedConnectionStatus::class,
            'last_synced_at' => 'immutable_datetime',
            'last_sync_started_at' => 'immutable_datetime',
            'last_sync_queued_at' => 'immutable_datetime',
        ];
    }
}
