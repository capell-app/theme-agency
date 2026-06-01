<?php

declare(strict_types=1);

namespace Capell\Payments\Filament\Resources\Subscriptions\Pages;

use Capell\Payments\Filament\Resources\Subscriptions\SubscriptionResource;
use Filament\Resources\Pages\ListRecords;

final class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;
}
