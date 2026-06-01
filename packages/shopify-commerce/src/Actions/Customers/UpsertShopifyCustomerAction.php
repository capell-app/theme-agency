<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Customers;

use Capell\ShopifyCommerce\Events\ShopifyCustomerSynced;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyCustomer;
use Carbon\CarbonInterface;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;

final class UpsertShopifyCustomerAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $snapshot
     */
    public function handle(ShopifyConnection $connection, array $snapshot): ShopifyCustomer
    {
        $shopifyGid = $this->stringValue($snapshot['id'] ?? $snapshot['admin_graphql_api_id'] ?? null);

        throw_if($shopifyGid === null, InvalidArgumentException::class, 'Shopify customer snapshots require an id.');

        /** @var ShopifyCustomer $customer */
        $customer = ShopifyCustomer::query()->updateOrCreate([
            'connection_id' => $connection->getKey(),
            'shopify_gid' => $shopifyGid,
        ], [
            'email' => $this->stringValue($snapshot['email'] ?? null),
            'first_name' => $this->stringValue($snapshot['firstName'] ?? $snapshot['first_name'] ?? null),
            'last_name' => $this->stringValue($snapshot['lastName'] ?? $snapshot['last_name'] ?? null),
            'phone' => $this->stringValue($snapshot['phone'] ?? null),
            'accepts_marketing' => $this->boolValue($snapshot['acceptsMarketing'] ?? $snapshot['accepts_marketing'] ?? false),
            'marketing_state' => $this->marketingState($snapshot),
            'orders_count' => $this->intValue($snapshot['numberOfOrders'] ?? $snapshot['orders_count'] ?? null) ?? 0,
            'total_spent_amount' => $this->moneyAmount($snapshot),
            'total_spent_currency' => $this->moneyCurrency($snapshot),
            'raw_snapshot' => $snapshot,
            'synced_at' => $this->syncedAt($snapshot),
        ]);

        event(new ShopifyCustomerSynced($customer->refresh()));

        return $customer;
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    private function marketingState(array $snapshot): ?string
    {
        $consent = $snapshot['emailMarketingConsent'] ?? null;

        if (is_array($consent)) {
            return $this->stringValue($consent['marketingState'] ?? $consent['marketing_state'] ?? null);
        }

        return $this->stringValue($snapshot['marketing_state'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    private function moneyAmount(array $snapshot): float
    {
        $amountSpent = $snapshot['amountSpent'] ?? null;

        if (is_array($amountSpent)) {
            return (float) ($this->stringValue($amountSpent['amount'] ?? null) ?? 0);
        }

        return (float) ($this->stringValue($snapshot['total_spent'] ?? null) ?? 0);
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    private function moneyCurrency(array $snapshot): ?string
    {
        $amountSpent = $snapshot['amountSpent'] ?? null;

        if (is_array($amountSpent)) {
            return $this->stringValue($amountSpent['currencyCode'] ?? $amountSpent['currency_code'] ?? null);
        }

        return $this->stringValue($snapshot['currency'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    private function syncedAt(array $snapshot): CarbonInterface
    {
        $syncedAt = $snapshot['synced_at'] ?? null;

        return $syncedAt instanceof CarbonInterface ? $syncedAt : now();
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function intValue(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function boolValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return is_string($value) && in_array(mb_strtolower($value), ['1', 'true', 'yes'], true);
    }
}
