<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Models;

use Capell\Core\Models\Site;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property string|null $access_token
 * @property string|null $bulk_operation_id
 * @property string|null $bulk_operation_url
 * @property CarbonImmutable|null $last_sync_started_at
 * @property CarbonImmutable|null $last_sync_queued_at
 * @property int $site_id
 * @property string|null $last_sync_error
 * @property string $shop_domain
 * @property string|null $sync_status
 * @property ShopifyConnectionStatus $status
 */
final class ShopifyConnection extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'shopify_connections';

    protected $guarded = [];

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * @return HasMany<ShopifyProduct, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(ShopifyProduct::class, 'connection_id');
    }

    public function isActive(): bool
    {
        return $this->status === ShopifyConnectionStatus::Active && is_string($this->access_token) && $this->access_token !== '';
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'scopes' => 'array',
            'status' => ShopifyConnectionStatus::class,
            'last_synced_at' => 'immutable_datetime',
            'last_sync_started_at' => 'immutable_datetime',
            'last_sync_queued_at' => 'immutable_datetime',
        ];
    }
}
