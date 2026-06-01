<?php

declare(strict_types=1);

namespace Capell\Payments\Support\CustomerPortal;

use Capell\CustomerPortal\Contracts\PortalDashboardItemProvider;
use Capell\CustomerPortal\Data\PortalDashboardItemData;
use Capell\CustomerPortal\Enums\PortalDashboardItemPriority;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\Payments\Actions\ResolvePortalPaymentCustomerAction;
use Capell\Payments\Enums\SubscriptionStatus;
use Capell\Payments\Models\PaymentCustomer;
use Capell\Payments\Models\Subscription;
use Illuminate\Support\Facades\Route;

final class PaymentsPortalDashboardItemProvider implements PortalDashboardItemProvider
{
    public function dashboardItemsFor(PortalAccount $portalAccount): iterable
    {
        $paymentCustomer = ResolvePortalPaymentCustomerAction::run($portalAccount);

        if (! $paymentCustomer instanceof PaymentCustomer) {
            return [];
        }

        return [
            new PortalDashboardItemData(
                key: 'payments.billing',
                label: __('capell-payments::generic.portal.billing_label'),
                description: $this->description($paymentCustomer),
                url: Route::has('capell-payments.portal.billing') ? route('capell-payments.portal.billing') : null,
                count: $this->activeSubscriptionCount($paymentCustomer),
                priority: PortalDashboardItemPriority::High,
                meta: [
                    'provider' => $paymentCustomer->provider->value,
                ],
            ),
        ];
    }

    private function description(PaymentCustomer $paymentCustomer): string
    {
        $latestSubscription = $paymentCustomer
            ->subscriptions()
            ->whereIn('status', [
                SubscriptionStatus::Active->value,
                SubscriptionStatus::Trialing->value,
                SubscriptionStatus::PastDue->value,
            ])
            ->latest('current_period_ends_at')
            ->first();

        if (! $latestSubscription instanceof Subscription) {
            return __('capell-payments::generic.portal.billing_description');
        }

        if ($latestSubscription->current_period_ends_at === null) {
            return __('capell-payments::generic.portal.billing_subscription_status', [
                'status' => $latestSubscription->status->getLabel(),
            ]);
        }

        return __('capell-payments::generic.portal.billing_subscription_period', [
            'status' => $latestSubscription->status->getLabel(),
            'date' => $latestSubscription->current_period_ends_at->toFormattedDateString(),
        ]);
    }

    private function activeSubscriptionCount(PaymentCustomer $paymentCustomer): int
    {
        return $paymentCustomer
            ->subscriptions()
            ->whereIn('status', [
                SubscriptionStatus::Active->value,
                SubscriptionStatus::Trialing->value,
            ])
            ->count();
    }
}
