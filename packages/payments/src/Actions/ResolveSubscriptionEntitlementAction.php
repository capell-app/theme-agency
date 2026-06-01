<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Data\SubscriptionEntitlementData;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\SubscriptionStatus;
use Capell\Payments\Models\PaymentCustomer;
use Capell\Payments\Models\Subscription;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class ResolveSubscriptionEntitlementAction
{
    use AsAction;

    public function handle(
        ?string $providerCustomerId = null,
        ?string $billableType = null,
        ?string $billableId = null,
        PaymentProvider $provider = PaymentProvider::Stripe,
        ?CarbonInterface $now = null,
    ): SubscriptionEntitlementData {
        $subscription = $this->subscription($providerCustomerId, $billableType, $billableId, $provider);

        if (! $subscription instanceof Subscription) {
            return new SubscriptionEntitlementData(entitled: false, provider: $provider, providerCustomerId: $providerCustomerId);
        }

        $currentTime = $now ?? CarbonImmutable::now();

        return new SubscriptionEntitlementData(
            entitled: $this->isEntitled($subscription, $currentTime),
            provider: $subscription->provider,
            providerCustomerId: $subscription->provider_customer_id,
            providerSubscriptionId: $subscription->provider_subscription_id,
            status: $subscription->status,
            trialEndsAt: $subscription->trial_ends_at,
            currentPeriodEndsAt: $subscription->current_period_ends_at,
            cancelAt: $subscription->cancel_at,
            canceledAt: $subscription->canceled_at,
        );
    }

    private function subscription(
        ?string $providerCustomerId,
        ?string $billableType,
        ?string $billableId,
        PaymentProvider $provider,
    ): ?Subscription {
        $query = Subscription::query()
            ->where('provider', $provider->value)
            ->whereIn('status', [
                SubscriptionStatus::Trialing->value,
                SubscriptionStatus::Active->value,
            ])
            ->orderByRaw('current_period_ends_at is null')
            ->latest('current_period_ends_at')
            ->latest('id');

        if ($providerCustomerId !== null && $providerCustomerId !== '') {
            return $query
                ->where('provider_customer_id', $providerCustomerId)
                ->first();
        }

        if ($billableType === null || $billableType === '' || $billableId === null || $billableId === '') {
            return null;
        }

        $customerIds = PaymentCustomer::query()
            ->where('provider', $provider->value)
            ->where('billable_type', $billableType)
            ->where('billable_id', $billableId)
            ->pluck('provider_customer_id')
            ->filter(static fn (mixed $value): bool => is_string($value) && $value !== '')
            ->values();

        if ($customerIds->isEmpty()) {
            return null;
        }

        return $query
            ->where(function (Builder $builder) use ($customerIds): void {
                $builder
                    ->whereIn('provider_customer_id', $customerIds->all())
                    ->orWhereHas('customer', function (Builder $customerQuery) use ($customerIds): void {
                        $customerQuery->whereIn('provider_customer_id', $customerIds->all());
                    });
            })
            ->first();
    }

    private function isEntitled(Subscription $subscription, CarbonInterface $now): bool
    {
        if (! in_array($subscription->status, [SubscriptionStatus::Trialing, SubscriptionStatus::Active], true)) {
            return false;
        }

        if ($subscription->canceled_at !== null && $subscription->canceled_at->lte($now)) {
            return false;
        }

        if ($subscription->status === SubscriptionStatus::Trialing) {
            return $subscription->trial_ends_at === null || $subscription->trial_ends_at->gte($now);
        }

        return $subscription->current_period_ends_at === null || $subscription->current_period_ends_at->gte($now);
    }
}
