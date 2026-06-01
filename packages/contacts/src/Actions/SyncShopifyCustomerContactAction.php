<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Data\ContactSourceRecordData;
use Capell\Contacts\Data\ContactSourceSyncResultData;
use Capell\Contacts\Enums\ContactActivityType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

final class SyncShopifyCustomerContactAction
{
    use AsAction;

    public function handle(object $event): ?ContactSourceSyncResultData
    {
        $customer = $event->customer ?? null;

        if (! $customer instanceof Model) {
            return null;
        }

        $customerId = $this->intValue($customer->getKey());
        $connection = $this->relatedModel($customer, 'connection');
        $siteId = $this->intValue($connection?->getAttribute('site_id'));

        if ($customerId === null || $siteId === null) {
            return null;
        }

        $shopifyGid = $this->stringValue($customer->getAttribute('shopify_gid')) ?? 'customer-' . $customerId;
        $syncedAt = $customer->getAttribute('synced_at');

        return SyncContactSourceRecordAction::run(
            new ContactSourceRecordData(
                siteId: $siteId,
                sourceKey: 'shopify_commerce',
                sourceIdentifier: $shopifyGid,
                email: $this->stringValue($customer->getAttribute('email')),
                phone: $this->stringValue($customer->getAttribute('phone')),
                firstName: $this->stringValue($customer->getAttribute('first_name')),
                lastName: $this->stringValue($customer->getAttribute('last_name')),
                profile: [
                    'shopify_commerce' => [
                        'connection_id' => $this->intValue($connection?->getKey()),
                        'shop_domain' => $this->stringValue($connection?->getAttribute('shop_domain')),
                        'customer_id' => $customerId,
                        'shopify_gid' => $shopifyGid,
                        'accepts_marketing' => (bool) $customer->getAttribute('accepts_marketing'),
                        'marketing_state' => $this->stringValue($customer->getAttribute('marketing_state')),
                        'orders_count' => $this->intValue($customer->getAttribute('orders_count')),
                    ],
                ],
                tags: ['shopify_commerce'],
                activityType: ContactActivityType::ShopifyCustomer,
                activitySummary: __('capell-contacts::generic.shopify_commerce.activity_summary'),
                activityPayload: [
                    'connection_id' => $this->intValue($connection?->getKey()),
                    'shop_domain' => $this->stringValue($connection?->getAttribute('shop_domain')),
                    'customer_id' => $customerId,
                    'shopify_gid' => $shopifyGid,
                    'accepts_marketing' => (bool) $customer->getAttribute('accepts_marketing'),
                    'marketing_state' => $this->stringValue($customer->getAttribute('marketing_state')),
                    'orders_count' => $this->intValue($customer->getAttribute('orders_count')),
                ],
                occurredAt: $syncedAt instanceof CarbonInterface ? $syncedAt : null,
            ),
            $customer,
        );
    }

    private function relatedModel(Model $model, string $relation): ?Model
    {
        $loaded = $model->relationLoaded($relation) ? $model->getRelation($relation) : null;

        if ($loaded instanceof Model) {
            return $loaded;
        }

        if (! $model->exists || ! method_exists($model, $relation)) {
            return null;
        }

        $related = $model->{$relation}()->first();

        return $related instanceof Model ? $related : null;
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
}
