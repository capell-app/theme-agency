<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Console\Commands;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Install\ConsoleProgressReporter;
use Capell\ShopifyCommerce\Actions\InstallShopifyCommercePackageAction;
use Illuminate\Console\Command;

final class InstallShopifyCommerceCommand extends Command
{
    protected $signature = 'capell-shopify-commerce:install';

    protected $description = 'Publish Shopify Commerce migrations, settings migrations, and config.';

    public function handle(): int
    {
        InstallShopifyCommercePackageAction::run(CapellCore::getPackage('capell-app/shopify-commerce'), [], new ConsoleProgressReporter($this));

        return self::SUCCESS;
    }
}
