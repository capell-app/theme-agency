<?php

declare(strict_types=1);

namespace Capell\Contacts\Listeners;

use Capell\Contacts\Actions\SyncShopifyCustomerContactAction;

final class SyncContactFromShopifyCustomer
{
    public function handle(object $event): void
    {
        SyncShopifyCustomerContactAction::run($event);
    }
}
