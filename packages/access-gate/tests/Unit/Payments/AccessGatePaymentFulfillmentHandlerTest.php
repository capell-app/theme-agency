<?php

declare(strict_types=1);

namespace Capell\AccessGate\Tests\Unit\Payments;

use Capell\AccessGate\Enums\GrantStatus;
use Capell\AccessGate\Enums\RegistrationStatus;
use Capell\AccessGate\Models\Grant;
use Capell\AccessGate\Models\Registration;
use Capell\AccessGate\Support\Payments\AccessGatePaymentFulfillmentHandler;
use Capell\AccessGate\Tests\PaymentsAccessGateTestCase;
use Capell\Payments\Actions\FulfillCompletedCheckoutSessionAction;
use Capell\Payments\Contracts\PaymentFulfillmentHandler;
use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Models\CheckoutSession;
use Illuminate\Support\Facades\Notification;

final class AccessGatePaymentFulfillmentHandlerTest extends PaymentsAccessGateTestCase
{
    public function test_it_registers_the_access_gate_payment_fulfilment_handler_through_the_payments_extension_tag(): void
    {
        $handlers = collect(app()->tagged(PaymentFulfillmentHandler::TAG));

        $this->assertTrue($handlers->contains(
            static fn (mixed $handler): bool => $handler instanceof AccessGatePaymentFulfillmentHandler,
        ));
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
