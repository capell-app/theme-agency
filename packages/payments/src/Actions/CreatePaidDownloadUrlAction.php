<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Models\PaymentDownloadEntitlement;
use Illuminate\Support\Facades\URL;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(PaymentDownloadEntitlement $entitlement, ?int $ttlMinutes = null)
 */
final class CreatePaidDownloadUrlAction
{
    use AsAction;

    public function handle(PaymentDownloadEntitlement $entitlement, ?int $ttlMinutes = null): string
    {
        $ttl = $ttlMinutes ?? (int) config('capell-payments.paid_downloads.signed_url_ttl_minutes', 60);

        return URL::temporarySignedRoute(
            name: 'capell-payments.paid-downloads.show',
            expiration: now()->addMinutes(max(1, $ttl)),
            parameters: [
                'entitlement' => $entitlement->getKey(),
            ],
        );
    }
}
