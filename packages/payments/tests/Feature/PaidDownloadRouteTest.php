<?php

declare(strict_types=1);

use Capell\Payments\Actions\CreatePaidDownloadUrlAction;
use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentDownloadEntitlement;
use Capell\Payments\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

uses(TestCase::class);

it('downloads a signed paid download and records the access', function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('downloads/guide.pdf', 'paid download contents');

    $entitlement = createPaymentDownloadEntitlement();

    $this->get(CreatePaidDownloadUrlAction::run($entitlement))
        ->assertSuccessful()
        ->assertDownload('guide.pdf');

    $entitlement->refresh();

    expect($entitlement->download_count)->toBe(1)
        ->and($entitlement->last_downloaded_at)->not->toBeNull();
});

it('rejects unsigned paid download requests', function (): void {
    $entitlement = createPaymentDownloadEntitlement();

    $this->get(route('capell-payments.paid-downloads.show', ['entitlement' => $entitlement]))
        ->assertForbidden();
});

it('returns gone when the paid download entitlement has expired', function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('downloads/guide.pdf', 'paid download contents');

    $entitlement = createPaymentDownloadEntitlement([
        'expires_at' => now()->subMinute(),
    ]);

    $this->get(CreatePaidDownloadUrlAction::run($entitlement))
        ->assertGone();

    expect($entitlement->refresh()->download_count)->toBe(0);
});

it('returns not found when the paid download file is missing', function (): void {
    Storage::fake('local');

    $entitlement = createPaymentDownloadEntitlement();

    $this->get(CreatePaidDownloadUrlAction::run($entitlement))
        ->assertNotFound();

    expect($entitlement->refresh()->download_count)->toBe(0);
});

/**
 * @param  array<string, mixed>  $attributes
 */
function createPaymentDownloadEntitlement(array $attributes = []): PaymentDownloadEntitlement
{
    $checkoutSession = CheckoutSession::query()->create([
        'provider' => PaymentProvider::Stripe,
        'provider_session_id' => 'cs_paid_download_' . str()->random(8),
        'mode' => CheckoutMode::Payment,
        'purpose' => PaymentPurpose::PaidDownload,
        'status' => CheckoutSessionStatus::Complete,
    ]);

    return PaymentDownloadEntitlement::query()->create(array_merge([
        'checkout_session_id' => $checkoutSession->getKey(),
        'download_key' => 'guide',
        'download_name' => 'Guide',
        'disk' => 'local',
        'path' => 'downloads/guide.pdf',
        'file_name' => 'guide.pdf',
        'expires_at' => now()->addHour(),
        'fulfilled_at' => now(),
        'download_count' => 0,
    ], $attributes));
}
