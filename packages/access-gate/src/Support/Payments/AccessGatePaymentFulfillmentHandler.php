<?php

declare(strict_types=1);

namespace Capell\AccessGate\Support\Payments;

use Capell\AccessGate\Actions\ApproveRegistrationAction;
use Capell\AccessGate\Enums\RegistrationStatus;
use Capell\AccessGate\Models\Grant;
use Capell\AccessGate\Models\Registration;
use Capell\Payments\Contracts\PaymentFulfillmentHandler;
use Capell\Payments\Data\PaymentFulfillmentResultData;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Models\CheckoutSession;

final class AccessGatePaymentFulfillmentHandler implements PaymentFulfillmentHandler
{
    public function __construct(private readonly ApproveRegistrationAction $approveRegistration) {}

    public function key(): string
    {
        return 'access-gate.registration';
    }

    public function supports(CheckoutSession $checkoutSession): bool
    {
        return $checkoutSession->purpose === PaymentPurpose::GatedAccess
            && $this->registrationId($checkoutSession) !== null;
    }

    public function fulfill(CheckoutSession $checkoutSession): PaymentFulfillmentResultData
    {
        $registrationId = $this->registrationId($checkoutSession);

        if ($registrationId === null) {
            return $this->result(false, 'missing_registration_reference');
        }

        $registration = Registration::query()->whereKey($registrationId)->first();

        if (! $registration instanceof Registration) {
            return $this->result(false, 'registration_not_found', [
                'registration_id' => $registrationId,
            ]);
        }

        if ($registration->status === RegistrationStatus::Pending) {
            $registration = $this->approveRegistration->handle($registration);
        }

        if (! in_array($registration->status, [RegistrationStatus::Approved, RegistrationStatus::Claimed], true)) {
            return $this->result(false, 'registration_not_fulfillable', [
                'registration_id' => (int) $registration->getKey(),
                'registration_status' => $registration->status->value,
            ]);
        }

        $grant = $registration
            ->grants()
            ->latest('id')
            ->first();

        return $this->result(true, 'registration_approved', [
            'registration_id' => (int) $registration->getKey(),
            'grant_id' => $grant instanceof Grant ? (int) $grant->getKey() : null,
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
            metadata: array_filter($metadata, static fn (mixed $value): bool => $value !== null),
        );
    }

    private function registrationId(CheckoutSession $checkoutSession): ?int
    {
        foreach ([
            $this->registrationIdFromTypedReference($checkoutSession->source_type, $checkoutSession->source_id),
            $this->registrationIdFromTypedReference($checkoutSession->payable_type, $checkoutSession->payable_id),
            $this->registrationIdFromMetadata($checkoutSession->metadata),
        ] as $registrationId) {
            if ($registrationId !== null) {
                return $registrationId;
            }
        }

        return null;
    }

    private function registrationIdFromTypedReference(?string $type, ?string $identifier): ?int
    {
        if (! is_numeric($identifier) || (int) $identifier < 1) {
            return null;
        }

        if (! in_array($type, ['access-gate.registration', 'access_gate.registration', Registration::class, 'registration'], true)) {
            return null;
        }

        return (int) $identifier;
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    private function registrationIdFromMetadata(?array $metadata): ?int
    {
        if ($metadata === null) {
            return null;
        }

        foreach (['access_gate_registration_id', 'registration_id'] as $key) {
            $value = $metadata[$key] ?? null;

            if (is_numeric($value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        return null;
    }
}
