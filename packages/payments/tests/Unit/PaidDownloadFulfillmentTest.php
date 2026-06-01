<?php

declare(strict_types=1);

use Capell\Payments\Actions\CreatePaidDownloadUrlAction;
use Capell\Payments\Actions\FulfillCompletedCheckoutSessionAction;
use Capell\Payments\Contracts\PaymentFulfillmentHandler;
use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentDownloadEntitlement;
use Capell\Payments\Support\Fulfillment\PaidDownloadFulfillmentHandler;
use Capell\Payments\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

uses(TestCase::class);

beforeEach(function (): void {
    app()->bind(PaidDownloadFulfillmentHandler::class);
    app()->tag([PaidDownloadFulfillmentHandler::class], PaymentFulfillmentHandler::TAG);
});

it('grants paid download entitlements from completed checkout sessions', function (): void {
    $checkoutSession = paidDownloadCheckoutSession([
        'provider_session_id' => 'cs_paid_download_123',
        'site_id' => 10,
        'payable_id' => 'guide',
        'metadata' => [
            'download_path' => 'paid/guide.pdf',
            'download_name' => 'Implementation Guide',
            'download_file_name' => 'guide.pdf',
            'download_disk' => 'local',
            'download_ttl_minutes' => 30,
        ],
    ]);

    $results = FulfillCompletedCheckoutSessionAction::run($checkoutSession);
    $entitlement = PaymentDownloadEntitlement::query()->firstOrFail();

    expect($results)->toHaveCount(1)
        ->and($results[0]->handler)->toBe('payments.paid-download')
        ->and($results[0]->fulfilled)->toBeTrue()
        ->and($results[0]->metadata)->toMatchArray([
            'entitlement_id' => $entitlement->getKey(),
            'download_key' => 'guide',
        ])
        ->and($entitlement->site_id)->toBe(10)
        ->and($entitlement->download_key)->toBe('guide')
        ->and($entitlement->download_name)->toBe('Implementation Guide')
        ->and($entitlement->path)->toBe('paid/guide.pdf')
        ->and($entitlement->file_name)->toBe('guide.pdf')
        ->and($entitlement->fulfilled_at)->not->toBeNull()
        ->and($entitlement->expires_at)->not->toBeNull();
});

it('keeps paid download fulfilment idempotent for the same checkout session', function (): void {
    $checkoutSession = paidDownloadCheckoutSession([
        'provider_session_id' => 'cs_paid_download_repeat',
        'payable_id' => 'guide',
        'metadata' => [
            'download_path' => 'paid/guide.pdf',
        ],
    ]);

    FulfillCompletedCheckoutSessionAction::run($checkoutSession);
    FulfillCompletedCheckoutSessionAction::run($checkoutSession);

    expect(PaymentDownloadEntitlement::query()->count())->toBe(1);
});

it('creates signed paid download URLs and streams entitled files without exposing package internals', function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('paid/guide.txt', 'paid download contents');

    $checkoutSession = paidDownloadCheckoutSession([
        'provider_session_id' => 'cs_paid_download_stream',
        'payable_id' => 'guide',
        'metadata' => [
            'download_path' => 'paid/guide.txt',
            'download_file_name' => 'guide.txt',
        ],
    ]);

    FulfillCompletedCheckoutSessionAction::run($checkoutSession);
    $entitlement = PaymentDownloadEntitlement::query()->firstOrFail();
    $url = CreatePaidDownloadUrlAction::run($entitlement, ttlMinutes: 10);

    expect($url)->toContain('/capell/payments/downloads/' . $entitlement->getKey())
        ->and($url)->toContain('signature=')
        ->and($url)->not->toContain('capell-app/payments')
        ->and($url)->not->toContain('Filament');

    $response = $this->get($url);

    $response->assertOk();

    expect($entitlement->refresh()->download_count)->toBe(1)
        ->and($entitlement->last_downloaded_at)->not->toBeNull();
});

it('does not expose paid downloads without a signed URL', function (): void {
    $checkoutSession = paidDownloadCheckoutSession([
        'provider_session_id' => 'cs_paid_download_unsigned',
        'payable_id' => 'guide',
        'metadata' => [
            'download_path' => 'paid/guide.pdf',
        ],
    ]);

    FulfillCompletedCheckoutSessionAction::run($checkoutSession);
    $entitlement = PaymentDownloadEntitlement::query()->firstOrFail();

    $this
        ->get(route('capell-payments.paid-downloads.show', ['entitlement' => $entitlement]))
        ->assertForbidden();
});

/**
 * @param  array<string, mixed>  $overrides
 */
function paidDownloadCheckoutSession(array $overrides = []): CheckoutSession
{
    return CheckoutSession::query()->create(array_replace([
        'provider' => PaymentProvider::Stripe,
        'provider_session_id' => 'cs_paid_download_' . str()->random(12),
        'mode' => CheckoutMode::Payment,
        'purpose' => PaymentPurpose::PaidDownload,
        'status' => CheckoutSessionStatus::Complete,
        'completed_at' => now(),
    ], $overrides));
}
