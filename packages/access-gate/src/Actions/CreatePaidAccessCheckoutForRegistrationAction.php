<?php

declare(strict_types=1);

namespace Capell\AccessGate\Actions;

use Capell\AccessGate\Data\CreatePaidAccessCheckoutData;
use Capell\AccessGate\Models\Registration;
use Capell\Payments\Actions\CreateCheckoutSessionAction;
use Capell\Payments\Data\CheckoutLineItemData;
use Capell\Payments\Data\CreateCheckoutSessionData;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Models\CheckoutSession;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static CheckoutSession run(Registration $registration, CreatePaidAccessCheckoutData $data)
 */
final class CreatePaidAccessCheckoutForRegistrationAction
{
    use AsAction;

    public function handle(Registration $registration, CreatePaidAccessCheckoutData $data): CheckoutSession
    {
        $this->validate($data);
        $registrationId = $this->modelKey($registration);
        $areaId = is_scalar($registration->access_area_id) ? (string) $registration->access_area_id : '';

        return CreateCheckoutSessionAction::run(new CreateCheckoutSessionData(
            successUrl: $data->successUrl,
            cancelUrl: $data->cancelUrl,
            lineItems: [
                new CheckoutLineItemData(
                    name: $data->lineItemName,
                    amount: $data->amount,
                    currency: $this->normalizedCurrency($data->currency),
                    quantity: $data->quantity,
                    description: $data->lineItemDescription,
                    providerPriceId: $data->providerPriceId,
                    metadata: [
                        'access_gate_registration_id' => $registrationId,
                        'access_gate_area_id' => $areaId,
                    ],
                ),
            ],
            purpose: PaymentPurpose::GatedAccess,
            mode: $data->mode,
            provider: $data->provider,
            siteId: $registration->area?->site_id,
            customerEmail: $registration->email,
            customerName: $data->customerName,
            payableType: 'access-gate.registration',
            payableId: $registrationId,
            sourceType: 'access-gate.registration',
            sourceId: $registrationId,
            referenceId: $data->referenceId ?? 'access-gate-registration-' . $registrationId,
            idempotencyKey: $data->idempotencyKey ?? 'access-gate-registration-' . $registrationId,
            metadata: array_replace($data->metadata, [
                'access_gate_registration_id' => $registrationId,
                'access_gate_area_id' => $areaId,
                'access_gate_area_key' => $registration->area?->key,
            ]),
        ));
    }

    private function validate(CreatePaidAccessCheckoutData $data): void
    {
        throw_if($data->amount < 1, InvalidArgumentException::class, 'Paid access checkout amount must be at least one minor currency unit.');
        throw_if($data->quantity < 1, InvalidArgumentException::class, 'Paid access checkout quantity must be at least one.');
        throw_if($data->lineItemName === '', InvalidArgumentException::class, 'Paid access checkout line item name cannot be empty.');
        throw_if(! preg_match('/^[a-zA-Z]{3}$/', $data->currency), InvalidArgumentException::class, 'Paid access checkout currency must be a three-letter ISO currency code.');
        throw_if(! $this->isAbsoluteHttpUrl($data->successUrl), InvalidArgumentException::class, 'Paid access checkout success URL must be an absolute HTTP URL.');
        throw_if(! $this->isAbsoluteHttpUrl($data->cancelUrl), InvalidArgumentException::class, 'Paid access checkout cancel URL must be an absolute HTTP URL.');
    }

    private function normalizedCurrency(string $currency): string
    {
        return strtolower($currency);
    }

    private function modelKey(Registration $registration): string
    {
        $key = $registration->getKey();

        throw_unless(is_scalar($key), InvalidArgumentException::class, 'Paid access registration must have a scalar key.');

        return (string) $key;
    }

    private function isAbsoluteHttpUrl(string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '' && in_array($scheme, ['http', 'https'], true);
    }
}
