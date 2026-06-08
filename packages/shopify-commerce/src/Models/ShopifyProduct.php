<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Models;

use Capell\ShopifyCommerce\Data\ShopifyProductOptionData;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;
use Spatie\LaravelData\DataCollection;

/**
 * @property string $shopify_gid
 * @property string $handle
 * @property string $title
 * @property string $status
 * @property DataCollection<int, ShopifyProductOptionData> $options
 * @property array<string, mixed>|null $featured_image
 * @property array<string, mixed> $raw_snapshot
 * @property string $search_text
 * @property CarbonImmutable|null $synced_at
 */
final class ShopifyProduct extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'shopify_products';

    protected $guarded = [];

    public static function searchableText(string $title, string $handle): string
    {
        return mb_strtolower(trim($title . ' ' . $handle));
    }

    /**
     * @return BelongsTo<ShopifyConnection, $this>
     */
    public function connection(): BelongsTo
    {
        return $this->belongsTo(ShopifyConnection::class, 'connection_id');
    }

    /**
     * @return HasMany<ShopifyProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ShopifyProductVariant::class, 'product_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'options' => DataCollection::class . ':' . ShopifyProductOptionData::class,
            'featured_image' => 'array',
            'raw_snapshot' => 'array',
            'synced_at' => 'immutable_datetime',
        ];
    }
}
