<?php

declare(strict_types=1);

namespace Capell\Payments\Filament\Resources\CheckoutSessions\Pages;

use Capell\Payments\Filament\Resources\CheckoutSessions\CheckoutSessionResource;
use Filament\Resources\Pages\ListRecords;

final class ListCheckoutSessions extends ListRecords
{
    protected static string $resource = CheckoutSessionResource::class;
}
