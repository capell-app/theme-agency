<?php

declare(strict_types=1);

namespace Capell\Payments\Http\Controllers;

use Capell\Payments\Actions\DownloadPaidDownloadAction;
use Capell\Payments\Models\PaymentDownloadEntitlement;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DownloadPaidDownloadController
{
    public function __invoke(PaymentDownloadEntitlement $entitlement): StreamedResponse
    {
        return DownloadPaidDownloadAction::run($entitlement);
    }
}
