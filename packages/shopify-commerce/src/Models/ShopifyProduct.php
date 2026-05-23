<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Models;

use Capell\ShopifyCommerce\Data\ShopifyProductOptionData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;
use Spatie\LaravelData\DataCollection;

final class ShopifyProduct extends Model
{
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
