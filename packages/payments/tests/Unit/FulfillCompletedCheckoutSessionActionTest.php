<?php

declare(strict_types=1);

use Capell\Payments\Actions\FulfillCompletedCheckoutSessionAction;
use Capell\Payments\Contracts\PaymentFulfillmentHandler;
use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Tests\Fakes\FakePaymentFulfillmentHandler;
use Capell\Payments\Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    FakePaymentFulfillmentHandler::$fulfilledSessionIds = [];
});

it('runs matching fulfilment handlers for completed checkout sessions', function (): void {
    app()->bind(FakePaymentFulfillmentHandler::class);
    app()->tag([FakePaymentFulfillmentHandler::class], PaymentFulfillmentHandler::TAG);

    $checkoutSession = CheckoutSession::query()->create([
        'provider' => PaymentProvider::Stripe->value,
        'provider_session_id' => 'cs_test_fulfillment',
        'mode' => CheckoutMode::Payment->value,
        'purpose' => PaymentPurpose::PaidDownload->value,
        'status' => CheckoutSessionStatus::Complete->value,
        'payable_type' => 'download',
        'payable_id' => 'guide',
    ]);

    $results = FulfillCompletedCheckoutSessionAction::run($checkoutSession);

    expect($results)->toHaveCount(1)
        ->and($results[0]->handler)->toBe('fake')
        ->and($results[0]->fulfilled)->toBeTrue()
        ->and(FakePaymentFulfillmentHandler::$fulfilledSessionIds)->toBe(['cs_test_fulfillment']);
});

it('does not fulfil incomplete checkout sessions', function (): void {
    app()->bind(FakePaymentFulfillmentHandler::class);
    app()->tag([FakePaymentFulfillmentHandler::class], PaymentFulfillmentHandler::TAG);

    $checkoutSession = CheckoutSession::query()->create([
        'provider' => PaymentProvider::Stripe->value,
        'provider_session_id' => 'cs_test_open',
        'mode' => CheckoutMode::Payment->value,
        'purpose' => PaymentPurpose::PaidDownload->value,
        'status' => CheckoutSessionStatus::Open->value,
        'payable_type' => 'download',
        'payable_id' => 'guide',
    ]);

    $results = FulfillCompletedCheckoutSessionAction::run($checkoutSession);

    expect($results)->toBe([])
        ->and(FakePaymentFulfillmentHandler::$fulfilledSessionIds)->toBe([]);
});
