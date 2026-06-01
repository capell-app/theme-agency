<?php

declare(strict_types=1);

namespace Capell\Payments\Support\Fulfillment;

use Capell\Payments\Actions\GrantPaidDownloadAccessAction;
use Capell\Payments\Contracts\PaymentFulfillmentHandler;
use Capell\Payments\Data\PaymentFulfillmentResultData;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Models\CheckoutSession;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

final class PaidDownloadFulfillmentHandler implements PaymentFulfillmentHandler
{
    public function key(): string
    {
        return 'payments.paid-download';
    }

    public function supports(CheckoutSession $checkoutSession): bool
    {
        return $checkoutSession->purpose === PaymentPurpose::PaidDownload
            && (is_string($checkoutSession->payable_id) || is_string(Arr::get($checkoutSession->metadata ?? [], 'download_key')))
            && is_string(Arr::get($checkoutSession->metadata ?? [], 'download_path'));
    }

    public function fulfill(CheckoutSession $checkoutSession): PaymentFulfillmentResultData
    {
        try {
            $entitlement = GrantPaidDownloadAccessAction::run($checkoutSession);
        } catch (ValidationException $exception) {
            return $this->result(false, 'missing_download_reference');
        }

        return $this->result(true, 'paid_download_entitlement_granted', [
            'entitlement_id' => (int) $entitlement->getKey(),
            'download_key' => $entitlement->download_key,
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function result(bool $fulfilled, string $message, array $metadata = []): PaymentFulfillmentResultData
    {
        return new PaymentFulfillmentResultData(
            handler: $this->key(),
            fulfilled: $fulfilled,
            message: $message,
            metadata: $metadata,
        );
    }
}
