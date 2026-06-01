<?php

declare(strict_types=1);

namespace Capell\Payments\Filament\Resources\Disputes\Pages;

use Capell\Payments\Filament\Resources\Disputes\PaymentDisputeResource;
use Filament\Resources\Pages\ListRecords;

final class ListPaymentDisputes extends ListRecords
{
    protected static string $resource = PaymentDisputeResource::class;
}
