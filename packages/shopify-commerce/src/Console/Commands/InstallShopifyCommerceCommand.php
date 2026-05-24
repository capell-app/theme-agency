<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Console\Commands;

use Capell\ShopifyCommerce\Actions\InstallShopifyCommercePermissionsAction;
use Illuminate\Console\Command;

final class InstallShopifyCommerceCommand extends Command
{
    protected $signature = 'capell-shopify-commerce:install';

    protected $description = 'Publish Shopify Commerce migrations, settings migrations, and config.';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'capell-shopify-commerce-config',
            '--force' => true,
        ]);

        $this->call('capell:publish-migrations', [
            '--items' => [
                '2026_05_22_000001_create_shopify_connections_table',
                '2026_05_22_000002_create_shopify_oauth_states_table',
                '2026_05_22_000003_create_shopify_products_table',
                '2026_05_22_000004_create_shopify_product_variants_table',
            ],
            '--path' => dirname(__DIR__, 3) . '/database/migrations',
        ]);

        $this->call('capell:publish-migrations', [
            '--type' => 'settings',
            '--items' => [
                '2026_05_22_000001_create_shopify_commerce_settings',
            ],
            '--path' => dirname(__DIR__, 3) . '/database/settings',
        ]);

        InstallShopifyCommercePermissionsAction::run();

        $this->info('Capell Shopify Commerce install files published.');

        return self::SUCCESS;
    }
}
