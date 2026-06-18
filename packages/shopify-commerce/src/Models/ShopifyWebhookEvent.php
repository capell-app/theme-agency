<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $connection_id
 * @property string $webhook_id
 * @property string $topic
 * @property CarbonImmutable|null $triggered_at
 * @property CarbonImmutable|null $received_at
 */
final class ShopifyWebhookEvent extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'shopify_webhook_events';

    protected $guarded = [];

    /**
     * @return BelongsTo<ShopifyConnection, $this>
     */
    public function connection(): BelongsTo
    {
        return $this->belongsTo(ShopifyConnection::class, 'connection_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'triggered_at' => 'immutable_datetime',
            'received_at' => 'immutable_datetime',
        ];
    }
}
