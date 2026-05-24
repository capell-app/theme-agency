<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Providers;

use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Facades\CapellCore;
use Capell\ShopifyCommerce\Actions\InstallShopifyCommercePermissionsAction;
use Capell\ShopifyCommerce\Console\Commands\InstallShopifyCommerceCommand;
use Capell\ShopifyCommerce\Console\Commands\SyncShopifyProductsCommand;
use Capell\ShopifyCommerce\Filament\Pages\ShopifyConnectionPage;
use Illuminate\Support\ServiceProvider;
use Override;

final class AdminServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (! CapellCore::isPackageInstalled(ShopifyCommerceServiceProvider::$packageName) || config('capell-shopify-commerce.enabled', true) !== true) {
            return;
        }

        InstallShopifyCommercePermissionsAction::run();

        CapellAdmin::registerExtensionPage(ShopifyCommerceServiceProvider::$packageName, ShopifyConnectionPage::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallShopifyCommerceCommand::class,
                SyncShopifyProductsCommand::class,
            ]);
        }
    }
}
