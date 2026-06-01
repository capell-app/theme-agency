<?php

declare(strict_types=1);

use Capell\FormBuilder\Data\SubmissionMetaData;
use Capell\FormBuilder\Data\SubmissionPayloadData;
use Capell\FormBuilder\Models\Form;
use Capell\FormBuilder\Models\Submission;
use Capell\Payments\Actions\CreateFormPaymentCheckoutSessionAction;
use Capell\Payments\Actions\CreateFormPaymentCheckoutUrlAction;
use Capell\Payments\Actions\ResolveFormPaymentCheckoutDataAction;
use Capell\Payments\Contracts\PaymentGateway;
use Capell\Payments\Data\CheckoutSessionData;
use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Tests\Fakes\FakePaymentGateway;
use Capell\Payments\Tests\FormBuilderPaymentsTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

uses(FormBuilderPaymentsTestCase::class);

it('creates form payment checkout sessions from portable Form Builder payment fields', function (): void {
    $form = paymentsFormBuilderForm();
    $submission = paymentsFormBuilderSubmission($form, [
        'email' => 'buyer@example.test',
        'donation' => 3500,
    ]);
    $gateway = new FakePaymentGateway(new CheckoutSessionData(
        provider: PaymentProvider::Stripe,
        providerSessionId: 'cs_form_payment_123',
        status: CheckoutSessionStatus::Open,
        mode: CheckoutMode::Payment,
        purpose: PaymentPurpose::FormPayment,
        url: 'https://checkout.stripe.com/c/pay/cs_form_payment_123',
        currency: 'gbp',
        amountSubtotal: 3500,
        amountTotal: 3500,
        customerEmail: 'buyer@example.test',
        metadata: [
            'form_id' => (string) $form->getKey(),
            'submission_id' => (string) $submission->getKey(),
            'field_key' => 'donation',
        ],
        providerPayload: ['id' => 'cs_form_payment_123'],
    ));

    app()->instance(PaymentGateway::class, $gateway);

    $checkoutSession = CreateFormPaymentCheckoutSessionAction::run(
        submission: $submission,
        successUrl: 'https://example.test/thanks',
        cancelUrl: 'https://example.test/retry',
    );

    expect($checkoutSession)->toBeInstanceOf(CheckoutSession::class)
        ->and($checkoutSession->purpose)->toBe(PaymentPurpose::FormPayment)
        ->and($checkoutSession->source_type)->toBe('form_builder.form')
        ->and($checkoutSession->source_id)->toBe((string) $form->getKey())
        ->and($checkoutSession->payable_type)->toBe('form_builder.submission')
        ->and($checkoutSession->payable_id)->toBe((string) $submission->getKey())
        ->and($checkoutSession->reference_id)->toBe('form-submission-' . $submission->getKey())
        ->and($checkoutSession->amount_total)->toBe(3500)
        ->and($gateway->lastRequest?->customerEmail)->toBe('buyer@example.test')
        ->and($gateway->lastRequest?->lineItems[0]->name)->toBe('Donation')
        ->and($gateway->lastRequest?->lineItems[0]->amount)->toBe(3500)
        ->and($gateway->lastRequest?->lineItems[0]->currency)->toBe('gbp')
        ->and($gateway->lastRequest?->idempotencyKey)->toBe('form-payment-' . $submission->getKey() . '-donation');
});

it('uses configured payment field amount before submitted amount', function (): void {
    $form = paymentsFormBuilderForm([
        [
            'key' => 'fixed_payment',
            'label' => 'Application fee',
            'type' => 'payment',
            'required' => true,
            'payment_amount_cents' => 1200,
            'payment_currency' => 'usd',
        ],
    ]);
    $submission = paymentsFormBuilderSubmission($form, [
        'fixed_payment' => 9999,
    ]);

    $paymentData = ResolveFormPaymentCheckoutDataAction::run($submission);

    expect($paymentData->amountCents)->toBe(1200)
        ->and($paymentData->currency)->toBe('usd')
        ->and($paymentData->successUrl)->toContain('/payments/form/success?submission=' . $submission->getKey())
        ->and($paymentData->cancelUrl)->toContain('/payments/form/cancel?submission=' . $submission->getKey());
});

it('rejects form payment checkout creation when the payment amount is invalid', function (): void {
    $submission = paymentsFormBuilderSubmission(paymentsFormBuilderForm(), [
        'email' => 'buyer@example.test',
        'donation' => 0,
    ]);

    ResolveFormPaymentCheckoutDataAction::run($submission);
})->throws(ValidationException::class);

it('creates signed public checkout URLs for form payment submissions', function (): void {
    $submission = paymentsFormBuilderSubmission(paymentsFormBuilderForm(), [
        'email' => 'buyer@example.test',
        'donation' => 3500,
    ]);

    $url = CreateFormPaymentCheckoutUrlAction::run(
        submission: $submission,
        successUrl: 'https://example.test/thanks',
        cancelUrl: 'https://example.test/retry',
        ttlMinutes: 15,
    );

    expect($url)->toContain('/capell/payments/forms/' . $submission->getKey() . '/checkout')
        ->and($url)->toContain('success_url=')
        ->and($url)->toContain('cancel_url=')
        ->and($url)->toContain('signature=')
        ->and($url)->not->toContain('capell-app/payments')
        ->and($url)->not->toContain('Filament');
});

it('redirects signed form payment checkout requests to the provider checkout URL', function (): void {
    $form = paymentsFormBuilderForm();
    $submission = paymentsFormBuilderSubmission($form, [
        'email' => 'buyer@example.test',
        'donation' => 3500,
    ]);
    $gateway = new FakePaymentGateway(new CheckoutSessionData(
        provider: PaymentProvider::Stripe,
        providerSessionId: 'cs_form_payment_route_123',
        status: CheckoutSessionStatus::Open,
        mode: CheckoutMode::Payment,
        purpose: PaymentPurpose::FormPayment,
        url: 'https://checkout.stripe.com/c/pay/cs_form_payment_route_123',
        currency: 'gbp',
        amountSubtotal: 3500,
        amountTotal: 3500,
        customerEmail: 'buyer@example.test',
        providerPayload: ['id' => 'cs_form_payment_route_123'],
    ));

    app()->instance(PaymentGateway::class, $gateway);

    $response = $this->get(CreateFormPaymentCheckoutUrlAction::run(
        submission: $submission,
        successUrl: 'https://example.test/thanks',
        cancelUrl: 'https://example.test/retry',
    ));

    $response->assertRedirect('https://checkout.stripe.com/c/pay/cs_form_payment_route_123');

    expect($gateway->lastRequest?->successUrl)->toBe('https://example.test/thanks')
        ->and($gateway->lastRequest?->cancelUrl)->toBe('https://example.test/retry')
        ->and(CheckoutSession::query()->where('provider_session_id', 'cs_form_payment_route_123')->exists())->toBeTrue();
});

it('rejects unsigned public form payment checkout requests', function (): void {
    $submission = paymentsFormBuilderSubmission(paymentsFormBuilderForm(), [
        'email' => 'buyer@example.test',
        'donation' => 3500,
    ]);

    $this
        ->get(route('capell-payments.form-builder.checkout', ['submission' => $submission]))
        ->assertForbidden();
});

/**
 * @param  list<array<string, mixed>>|null  $paymentFields
 */
function paymentsFormBuilderForm(?array $paymentFields = null): Form
{
    $siteId = DB::table('sites')->insertGetId([]);

    return Form::query()->create([
        'site_id' => $siteId,
        'name' => 'Donation form',
        'handle' => 'donation-form-' . str()->random(8),
        'description' => null,
        'schema' => [
            [
                'key' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'required' => true,
            ],
            ...($paymentFields ?? [
                [
                    'key' => 'donation',
                    'label' => 'Donation',
                    'type' => 'payment',
                    'required' => true,
                    'payment_currency' => 'gbp',
                ],
            ]),
        ],
        'settings' => [],
        'is_active' => true,
    ]);
}

/**
 * @param  array<string, mixed>  $values
 */
function paymentsFormBuilderSubmission(Form $form, array $values): Submission
{
    return Submission::query()->create([
        'form_id' => $form->getKey(),
        'site_id' => $form->site_id,
        'payload' => new SubmissionPayloadData($values),
        'meta' => new SubmissionMetaData(url: 'https://example.test/donate'),
        'status' => 'new',
        'submitted_at' => now(),
    ]);
}
