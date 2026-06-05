<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Models\PaymentDownloadEntitlement;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @method static StreamedResponse run(PaymentDownloadEntitlement $entitlement)
 */
final class DownloadPaidDownloadAction
{
    use AsAction;

    public function handle(PaymentDownloadEntitlement $entitlement): StreamedResponse
    {
        abort_if($entitlement->expires_at !== null && $entitlement->expires_at->isPast(), Response::HTTP_GONE);
        abort_unless(Storage::disk($entitlement->disk)->exists($entitlement->path), Response::HTTP_NOT_FOUND);

        $entitlement->forceFill([
            'download_count' => (int) $entitlement->download_count + 1,
            'last_downloaded_at' => now(),
        ])->save();

        return Storage::disk($entitlement->disk)->download(
            path: $entitlement->path,
            name: $entitlement->file_name,
        );
    }
}
