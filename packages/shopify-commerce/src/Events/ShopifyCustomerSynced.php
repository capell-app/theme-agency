<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Events;

use Capell\ShopifyCommerce\Models\ShopifyCustomer;
use Illuminate\Foundation\Events\Dispatchable;

final class ShopifyCustomerSynced
{
    use Dispatchable;

    public function __construct(
        public readonly ShopifyCustomer $customer,
    ) {}
}
