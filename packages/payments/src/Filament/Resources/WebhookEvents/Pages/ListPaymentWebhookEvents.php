<?php

declare(strict_types=1);

namespace Capell\Payments\Filament\Resources\WebhookEvents\Pages;

use Capell\Payments\Filament\Resources\WebhookEvents\PaymentWebhookEventResource;
use Filament\Resources\Pages\ListRecords;

final class ListPaymentWebhookEvents extends ListRecords
{
    protected static string $resource = PaymentWebhookEventResource::class;
}
