<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\CustomerPortal\Models\PortalAccount;
use Capell\Payments\Models\PaymentCustomer;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PaymentCustomer|null run(PortalAccount $portalAccount)
 */
final class ResolvePortalPaymentCustomerAction
{
    use AsAction;

    public function handle(PortalAccount $portalAccount): ?PaymentCustomer
    {
        $paymentCustomer = $this->forPortalOwner($portalAccount)
            ?? $this->forPortalAccountMetadata($portalAccount)
            ?? $this->forPortalEmail($portalAccount);

        if (! $paymentCustomer instanceof PaymentCustomer) {
            return null;
        }

        return $paymentCustomer->provider_customer_id !== null && $paymentCustomer->provider_customer_id !== ''
            ? $paymentCustomer
            : null;
    }

    private function forPortalOwner(PortalAccount $portalAccount): ?PaymentCustomer
    {
        if ($portalAccount->owner_type === null || $portalAccount->owner_id === null) {
            return null;
        }

        return $this->baseQuery($portalAccount)
            ->where('billable_type', $portalAccount->owner_type)
            ->where('billable_id', (string) $portalAccount->owner_id)
            ->first();
    }

    private function forPortalAccountMetadata(PortalAccount $portalAccount): ?PaymentCustomer
    {
        return $this->baseQuery($portalAccount)
            ->where('metadata->portal_account_id', $portalAccount->getKey())
            ->first();
    }

    private function forPortalEmail(PortalAccount $portalAccount): ?PaymentCustomer
    {
        $email = PortalAccount::normalizeEmail($portalAccount->email);

        if ($email === null) {
            return null;
        }

        return $this->baseQuery($portalAccount)
            ->limit(100)
            ->get()
            ->first(static fn (PaymentCustomer $paymentCustomer): bool => PortalAccount::normalizeEmail($paymentCustomer->email) === $email);
    }

    /**
     * @return Builder<PaymentCustomer>
     */
    private function baseQuery(PortalAccount $portalAccount): Builder
    {
        return PaymentCustomer::query()
            ->where('site_id', $portalAccount->site_id)
            ->whereNotNull('provider_customer_id')
            ->latest('updated_at')
            ->latest('id');
    }
}
