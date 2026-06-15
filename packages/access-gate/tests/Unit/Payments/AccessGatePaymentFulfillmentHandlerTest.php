<?php

declare(strict_types=1);

namespace Capell\AccessGate\Tests\Unit\Payments;

use Capell\AccessGate\Actions\CreatePaidAccessCheckoutForRegistrationAction;
use Capell\AccessGate\Data\CreatePaidAccessCheckoutData;
use Capell\AccessGate\Enums\GrantStatus;
use Capell\AccessGate\Enums\RegistrationStatus;
use Capell\AccessGate\Models\Grant;
use Capell\AccessGate\Models\Registration;
use Capell\AccessGate\Support\Payments\AccessGatePaymentFulfillmentHandler;
use Capell\AccessGate\Tests\PaymentsAccessGateTestCase;
use Capell\Payments\Actions\FulfillCompletedCheckoutSessionAction;
use Capell\Payments\Contracts\PaymentFulfillmentHandler;
use Capell\Payments\Contracts\PaymentGateway;
use Capell\Payments\Data\CheckoutSessionData;
use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Tests\Fakes\FakePaymentGateway;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

final class AccessGatePaymentFulfillmentHandlerTest extends PaymentsAccessGateTestCase
{
    public function test_it_registers_the_access_gate_payment_fulfilment_handler_through_the_payments_extension_tag(): void
    {
        $handlers = collect(app()->tagged(PaymentFulfillmentHandler::TAG));

        $this->assertTrue($handlers->contains(
            static fn (mixed $handler): bool => $handler instanceof AccessGatePaymentFulfillmentHandler,
        ));
    }

    public function test_it_creates_paid_access_checkout_sessions_for_registrations_through_payments(): void
    {
        $registration = Registration::factory()->create([
            'email' => 'paid-reader@example.test',
            'email_normalized' => 'paid-reader@example.test',
        ]);
        $gateway = new FakePaymentGateway(new CheckoutSessionData(
            provider: PaymentProvider::Stripe,
            providerSessionId: 'cs_access_gate_registration',
            status: CheckoutSessionStatus::Open,
            mode: CheckoutMode::Payment,
            purpose: PaymentPurpose::GatedAccess,
            url: 'https://checkout.stripe.test/session/cs_access_gate_registration',
            currency: 'gbp',
            amountSubtotal: 4900,
            amountTotal: 4900,
            customerEmail: 'paid-reader@example.test',
            metadata: [
                'provider' => 'fake',
            ],
            providerPayload: [
                'id' => 'cs_access_gate_registration',
            ],
        ));

        app()->instance(PaymentGateway::class, $gateway);

        $checkoutSession = CreatePaidAccessCheckoutForRegistrationAction::run(
            $registration,
            new CreatePaidAccessCheckoutData(
                successUrl: 'https://example.test/members/thanks',
                cancelUrl: 'https://example.test/members/request',
                lineItemName: 'Members area access',
                amount: 4900,
                currency: 'GBP',
                metadata: [
                    'campaign' => 'launch',
                ],
            ),
        );

        $this->assertSame(PaymentPurpose::GatedAccess, $checkoutSession->purpose);
        $this->assertSame('access-gate.registration', $checkoutSession->source_type);
        $this->assertSame((string) $registration->getKey(), $checkoutSession->source_id);
        $this->assertSame('access-gate.registration', $checkoutSession->payable_type);
        $this->assertSame((string) $registration->getKey(), $checkoutSession->payable_id);
        $this->assertSame('access-gate-registration-' . $registration->getKey(), $checkoutSession->reference_id);
        $this->assertNotNull($gateway->lastRequest);

        $request = $gateway->lastRequest;

        $this->assertSame('access-gate-registration-' . $registration->getKey(), $request->idempotencyKey);
        $this->assertSame('paid-reader@example.test', $request->customerEmail);
        $this->assertSame('gbp', $request->lineItems[0]->currency);
        $this->assertSame('access-gate.registration', $request->sourceType);
        $this->assertSame((string) $registration->getKey(), $request->metadata['access_gate_registration_id'] ?? null);
        $this->assertSame('launch', $request->metadata['campaign'] ?? null);
    }

    public function test_it_rejects_unsafe_paid_access_checkout_creation_input(): void
    {
        $registration = Registration::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Paid access checkout amount must be at least one minor currency unit.');

        CreatePaidAccessCheckoutForRegistrationAction::run(
            $registration,
            new CreatePaidAccessCheckoutData(
                successUrl: 'https://example.test/members/thanks',
                cancelUrl: 'https://example.test/members/request',
                lineItemName: 'Members area access',
                amount: 0,
                currency: 'GBP',
            ),
        );
    }

    public function test_it_approves_pending_access_gate_registrations_from_completed_gated_access_checkout_sessions(): void
    {
        Notification::fake();

        $registration = Registration::factory()->create([
            'email' => 'customer@example.test',
            'email_normalized' => 'customer@example.test',
            'status' => RegistrationStatus::Pending,
        ]);
        $checkoutSession = $this->accessGatePaymentCheckoutSession([
            'source_type' => 'access-gate.registration',
            'source_id' => (string) $registration->getKey(),
        ]);

        $results = FulfillCompletedCheckoutSessionAction::run($checkoutSession);
        $grant = Grant::query()->where('registration_id', $registration->getKey())->firstOrFail();

        $this->assertCount(1, $results);
        $this->assertSame('access-gate.registration', $results[0]->handler);
        $this->assertTrue($results[0]->fulfilled);
        $this->assertSame((int) $registration->getKey(), $results[0]->metadata['registration_id'] ?? null);
        $this->assertSame((int) $grant->getKey(), $results[0]->metadata['grant_id'] ?? null);
        $this->assertSame(RegistrationStatus::Approved, $registration->refresh()->status);
        $this->assertSame(GrantStatus::Active, $grant->status);
    }

    public function test_it_keeps_access_gate_payment_fulfilment_idempotent_for_already_approved_registrations(): void
    {
        Notification::fake();

        $registration = Registration::factory()->create([
            'status' => RegistrationStatus::Approved,
            'approved_at' => now(),
        ]);
        $grant = Grant::factory()
            ->for($registration, 'registration')
            ->for($registration->area, 'area')
            ->create();
        $checkoutSession = $this->accessGatePaymentCheckoutSession([
            'metadata' => [
                'access_gate_registration_id' => $registration->getKey(),
            ],
        ]);

        $results = FulfillCompletedCheckoutSessionAction::run($checkoutSession);

        $this->assertCount(1, $results);
        $this->assertTrue($results[0]->fulfilled);
        $this->assertSame((int) $registration->getKey(), $results[0]->metadata['registration_id'] ?? null);
        $this->assertSame((int) $grant->getKey(), $results[0]->metadata['grant_id'] ?? null);
        $this->assertSame(1, Grant::query()->where('registration_id', $registration->getKey())->count());
    }

    public function test_it_does_not_fulfil_non_gated_access_checkout_sessions(): void
    {
        $registration = Registration::factory()->create();
        $checkoutSession = $this->accessGatePaymentCheckoutSession([
            'purpose' => PaymentPurpose::OneOff,
            'source_type' => 'access-gate.registration',
            'source_id' => (string) $registration->getKey(),
        ]);

        $this->assertSame([], FulfillCompletedCheckoutSessionAction::run($checkoutSession));
        $this->assertSame(RegistrationStatus::Pending, $registration->refresh()->status);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function accessGatePaymentCheckoutSession(array $overrides = []): CheckoutSession
    {
        return CheckoutSession::query()->create(array_replace([
            'provider' => PaymentProvider::Stripe,
            'provider_session_id' => 'cs_access_gate_' . str()->random(12),
            'mode' => CheckoutMode::Payment,
            'purpose' => PaymentPurpose::GatedAccess,
            'status' => CheckoutSessionStatus::Complete,
            'completed_at' => now(),
        ], $overrides));
    }
}
