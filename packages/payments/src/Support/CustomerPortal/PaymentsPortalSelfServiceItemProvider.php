<?php

declare(strict_types=1);

namespace Capell\Payments\Support\CustomerPortal;

use Capell\CustomerPortal\Contracts\PortalSelfServiceItemProvider;
use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Enums\PortalSelfServiceItemType;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\Payments\Actions\FormatPaymentMoneyAction;
use Capell\Payments\Actions\ResolvePortalPaymentCustomerAction;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentCustomer;
use Capell\Payments\Models\PaymentDownloadEntitlement;
use Capell\Payments\Models\Subscription;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

final class PaymentsPortalSelfServiceItemProvider implements PortalSelfServiceItemProvider
{
    /**
     * @return iterable<PortalSelfServiceItemData>
     */
    public function selfServiceItemsFor(PortalAccount $portalAccount): iterable
    {
        $paymentCustomer = ResolvePortalPaymentCustomerAction::run($portalAccount);

        if (! $paymentCustomer instanceof PaymentCustomer) {
            return [];
        }

        return [
            ...$this->subscriptionItems($paymentCustomer),
            ...$this->checkoutSessionItems($paymentCustomer),
            ...$this->downloadItems($paymentCustomer),
        ];
    }

    /**
     * @return list<PortalSelfServiceItemData>
     */
    private function subscriptionItems(PaymentCustomer $paymentCustomer): array
    {
        return $paymentCustomer
            ->subscriptions()
            ->latest('current_period_ends_at')
            ->limit(5)
            ->get()
            ->map(fn (Subscription $subscription): PortalSelfServiceItemData => new PortalSelfServiceItemData(
                key: 'payments.subscription.' . $subscription->getKey(),
                type: PortalSelfServiceItemType::Payment,
                label: __('capell-payments::generic.portal.subscription_label'),
                description: $this->subscriptionDescription($subscription),
                url: $this->billingUrl(),
                status: $subscription->status->getLabel(),
                occurredAt: $subscription->current_period_ends_at ?? $subscription->updated_at,
                meta: [
                    'provider' => $subscription->provider->value,
                    'provider_subscription_id' => $subscription->provider_subscription_id,
                ],
            ))
            ->all();
    }

    /**
     * @return list<PortalSelfServiceItemData>
     */
    private function checkoutSessionItems(PaymentCustomer $paymentCustomer): array
    {
        return $paymentCustomer
            ->checkoutSessions()
            ->where('status', CheckoutSessionStatus::Complete->value)
            ->latest('completed_at')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (CheckoutSession $checkoutSession): PortalSelfServiceItemData => new PortalSelfServiceItemData(
                key: 'payments.checkout-session.' . $checkoutSession->getKey(),
                type: PortalSelfServiceItemType::Payment,
                label: $checkoutSession->purpose->getLabel(),
                description: $this->checkoutDescription($checkoutSession),
                url: $this->billingUrl(),
                status: $checkoutSession->status->getLabel(),
                occurredAt: $checkoutSession->completed_at ?? $checkoutSession->updated_at,
                meta: [
                    'provider' => $checkoutSession->provider->value,
                    'purpose' => $checkoutSession->purpose->value,
                    'reference_id' => $checkoutSession->reference_id,
                ],
            ))
            ->all();
    }

    /**
     * @return list<PortalSelfServiceItemData>
     */
    private function downloadItems(PaymentCustomer $paymentCustomer): array
    {
        $checkoutSessionIds = $paymentCustomer
            ->checkoutSessions()
            ->pluck('id')
            ->map(fn (int|string $checkoutSessionId): int => (int) $checkoutSessionId)
            ->all();

        if ($checkoutSessionIds === []) {
            return [];
        }

        return PaymentDownloadEntitlement::query()
            ->whereIn('checkout_session_id', $checkoutSessionIds)
            ->latest('fulfilled_at')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (PaymentDownloadEntitlement $entitlement): PortalSelfServiceItemData => new PortalSelfServiceItemData(
                key: 'payments.download.' . $entitlement->getKey(),
                type: PortalSelfServiceItemType::Payment,
                label: $entitlement->download_name,
                description: $this->downloadDescription($entitlement),
                url: $this->downloadUrl($entitlement),
                status: $entitlement->expires_at?->isPast() === true
                    ? __('capell-payments::generic.portal.download_expired')
                    : __('capell-payments::generic.portal.download_ready'),
                occurredAt: $entitlement->fulfilled_at ?? $entitlement->updated_at,
                meta: [
                    'download_key' => $entitlement->download_key,
                    'download_count' => $entitlement->download_count,
                ],
            ))
            ->all();
    }

    private function subscriptionDescription(Subscription $subscription): string
    {
        if ($subscription->current_period_ends_at === null) {
            return __('capell-payments::generic.portal.subscription_description', [
                'status' => $subscription->status->getLabel(),
            ]);
        }

        return __('capell-payments::generic.portal.subscription_period_description', [
            'status' => $subscription->status->getLabel(),
            'date' => $subscription->current_period_ends_at->toFormattedDateString(),
        ]);
    }

    private function checkoutDescription(CheckoutSession $checkoutSession): string
    {
        if ($checkoutSession->amount_total === null || $checkoutSession->currency === null) {
            return __('capell-payments::generic.portal.checkout_description');
        }

        return __('capell-payments::generic.portal.checkout_amount_description', [
            'amount' => FormatPaymentMoneyAction::run($checkoutSession->amount_total, $checkoutSession->currency),
            'currency' => '',
        ]);
    }

    private function downloadDescription(PaymentDownloadEntitlement $entitlement): string
    {
        if ($entitlement->expires_at === null) {
            return __('capell-payments::generic.portal.download_description');
        }

        return __('capell-payments::generic.portal.download_expires_description', [
            'date' => $entitlement->expires_at->toFormattedDateString(),
        ]);
    }

    private function billingUrl(): ?string
    {
        return Route::has('capell-payments.portal.billing') ? route('capell-payments.portal.billing') : null;
    }

    private function downloadUrl(PaymentDownloadEntitlement $entitlement): ?string
    {
        if (! Route::has('capell-payments.paid-downloads.show')) {
            return null;
        }

        return URL::temporarySignedRoute(
            'capell-payments.paid-downloads.show',
            now()->addMinutes(30),
            ['entitlement' => $entitlement],
        );
    }
}
