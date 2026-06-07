<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Catalog;

use Capell\ShopifyCommerce\Data\ShopifyProductData;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Carbon\CarbonInterface;
use Lorisleiva\Actions\Concerns\AsAction;

final class PersistShopifyProductAction
{
    use AsAction;

    public function handle(ShopifyConnection $connection, ShopifyProductData $product, CarbonInterface $syncedAt): ShopifyProduct
    {
        /** @var ShopifyProduct $model */
        $model = ShopifyProduct::query()->updateOrCreate(
            [
                'connection_id' => $connection->getKey(),
                'shopify_gid' => $product->shopifyGid,
            ],
            [
                'handle' => $product->handle,
                'title' => $product->title,
                'search_text' => ShopifyProduct::searchableText($product->title, $product->handle),
                'status' => $product->status,
                'options' => $product->options,
                'featured_image' => $product->featuredImage,
                'raw_snapshot' => $product->rawSnapshot,
                'synced_at' => $syncedAt,
            ],
        );

        $seenVariantGids = [];

        foreach ($product->variants as $variant) {
            $seenVariantGids[] = $variant->shopifyGid;

            $model->variants()->updateOrCreate(
                ['shopify_gid' => $variant->shopifyGid],
                [
                    'title' => $variant->title,
                    'price_amount' => $variant->priceAmount,
                    'price_currency' => $variant->priceCurrency,
                    'available_for_sale' => $variant->availableForSale,
                    'selected_options' => $variant->selectedOptions,
                ],
            );
        }

        if ($seenVariantGids === []) {
            $model->variants()->delete();

            return $model;
        }

        $model->variants()->whereNotIn('shopify_gid', $seenVariantGids)->delete();

        return $model;
    }
}
