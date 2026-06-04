<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentDownloadEntitlement;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PaymentDownloadEntitlement run(CheckoutSession $checkoutSession)
 */
final class GrantPaidDownloadAccessAction
{
    use AsAction;

    public function handle(CheckoutSession $checkoutSession): PaymentDownloadEntitlement
    {
        $metadata = $checkoutSession->metadata ?? [];
        $downloadKey = $this->stringValue($checkoutSession->payable_id) ?? $this->stringValue(Arr::get($metadata, 'download_key'));
        $path = $this->stringValue(Arr::get($metadata, 'download_path'));

        if ($downloadKey === null || $path === null) {
            throw ValidationException::withMessages([
                'download' => __('capell-payments::generic.paid_downloads.missing_download_reference'),
            ]);
        }

        $entitlement = PaymentDownloadEntitlement::query()->firstOrNew([
            'checkout_session_id' => $checkoutSession->getKey(),
            'download_key' => $downloadKey,
        ]);
        $entitlement->forceFill([
            'site_id' => $checkoutSession->site_id,
            'download_name' => $this->stringValue(Arr::get($metadata, 'download_name')) ?? $downloadKey,
            'disk' => $this->stringValue(Arr::get($metadata, 'download_disk')) ?? config('capell-payments.paid_downloads.default_disk', 'local'),
            'path' => $path,
            'file_name' => $this->stringValue(Arr::get($metadata, 'download_file_name')),
            'email' => $checkoutSession->customer?->email,
            'metadata' => array_filter([
                'provider_session_id' => $checkoutSession->provider_session_id,
                'reference_id' => $checkoutSession->reference_id,
            ], static fn (?string $value): bool => $value !== null && $value !== ''),
        ]);

        if (! $entitlement->exists) {
            $entitlement->forceFill([
                'expires_at' => now()->addMinutes($this->ttlMinutes($metadata)),
                'fulfilled_at' => now(),
            ]);
        }

        $entitlement->save();

        return $entitlement;
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function ttlMinutes(array $metadata): int
    {
        $value = Arr::get($metadata, 'download_ttl_minutes', config('capell-payments.paid_downloads.entitlement_ttl_minutes', 10080));

        return is_numeric($value) ? max(1, (int) $value) : 10080;
    }

    private function stringValue(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
