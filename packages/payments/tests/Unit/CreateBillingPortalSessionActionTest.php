<?php

declare(strict_types=1);

use Capell\Payments\Actions\CreateBillingPortalSessionAction;
use Capell\Payments\Data\CreateBillingPortalSessionData;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Exceptions\PaymentGatewayConfigurationException;
use Capell\Payments\Tests\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(TestCase::class);

it('creates a native Stripe billing portal session', function (): void {
    config()->set('capell-payments.stripe.secret_key', 'sk_test_123');

    Http::fake([
        'https://api.stripe.com/v1/billing_portal/sessions' => Http::response([
            'id' => 'bps_test_123',
            'object' => 'billing_portal.session',
            'configuration' => 'bpc_test_123',
            'created' => 1_685_000_000,
            'customer' => 'cus_123',
            'livemode' => false,
            'locale' => 'en-GB',
            'return_url' => 'https://example.test/account',
            'url' => 'https://billing.stripe.com/p/session/test_123',
        ]),
    ]);

    $sessionData = CreateBillingPortalSessionAction::run(new CreateBillingPortalSessionData(
        providerCustomerId: 'cus_123',
        returnUrl: 'https://example.test/account',
        configuration: 'bpc_test_123',
        locale: 'en-GB',
    ));

    Http::assertSent(function (Request $request): bool {
        parse_str($request->body(), $body);

        return $request->hasHeader('Stripe-Version', '2026-02-25.clover')
            && $body['customer'] === 'cus_123'
            && $body['return_url'] === 'https://example.test/account'
            && $body['configuration'] === 'bpc_test_123'
            && $body['locale'] === 'en-GB';
    });

    expect($sessionData->provider)->toBe(PaymentProvider::Stripe)
        ->and($sessionData->providerSessionId)->toBe('bps_test_123')
        ->and($sessionData->providerCustomerId)->toBe('cus_123')
        ->and($sessionData->returnUrl)->toBe('https://example.test/account')
        ->and($sessionData->url)->toBe('https://billing.stripe.com/p/session/test_123')
        ->and($sessionData->createdAt)->not->toBeNull();
});

it('requires a Stripe secret before creating billing portal sessions', function (): void {
    config()->set('capell-payments.stripe.secret_key', null);

    CreateBillingPortalSessionAction::run(new CreateBillingPortalSessionData(
        providerCustomerId: 'cus_123',
        returnUrl: 'https://example.test/account',
    ));
})->throws(PaymentGatewayConfigurationException::class);
