<?php

declare(strict_types=1);

namespace Capell\Payments\Filament\Resources\Customers\Pages;

use Capell\Payments\Filament\Resources\Customers\PaymentCustomerResource;
use Filament\Resources\Pages\ListRecords;

final class ListPaymentCustomers extends ListRecords
{
    protected static string $resource = PaymentCustomerResource::class;
}
