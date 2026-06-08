<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Models;

use Capell\ShopifyCommerce\Data\ShopifyProductOptionData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;
use Spatie\LaravelData\DataCollection;

/**
 * @property string $shopify_gid
 * @property string $title
 * @property string $price_amount
 * @property string $price_currency
 * @property bool $available_for_sale
 * @property DataCollection<int, ShopifyProductOptionData> $selected_options
 */
final class ShopifyProductVariant extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'shopify_product_variants';

    protected $guarded = [];

    /**
     * @return BelongsTo<ShopifyProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopifyProduct::class, 'product_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'price_amount' => 'decimal:6',
            'available_for_sale' => 'bool',
            'selected_options' => DataCollection::class . ':' . ShopifyProductOptionData::class,
        ];
    }
}
