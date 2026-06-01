<?php

declare(strict_types=1);

namespace Capell\Payments\Filament\Resources\Refunds\Pages;

use Capell\Payments\Filament\Resources\Refunds\PaymentRefundResource;
use Filament\Resources\Pages\ListRecords;

final class ListPaymentRefunds extends ListRecords
{
    protected static string $resource = PaymentRefundResource::class;
}
