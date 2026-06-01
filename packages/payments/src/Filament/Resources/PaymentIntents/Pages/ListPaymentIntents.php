<?php

declare(strict_types=1);

namespace Capell\Payments\Filament\Resources\PaymentIntents\Pages;

use Capell\Payments\Filament\Resources\PaymentIntents\PaymentIntentResource;
use Filament\Resources\Pages\ListRecords;

final class ListPaymentIntents extends ListRecords
{
    protected static string $resource = PaymentIntentResource::class;
}
