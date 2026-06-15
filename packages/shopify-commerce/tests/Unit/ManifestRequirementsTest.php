<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\Core\Contracts\Extensions\RegistersExtensionSetting;
use Capell\Core\Contracts\Extensions\RunsScheduledExtensionJob;
use Capell\ShopifyCommerce\Console\Commands\InstallShopifyCommerceCommand;
use Capell\ShopifyCommerce\Console\Commands\PruneExpiredShopifyOAuthStatesCommand;
use Capell\ShopifyCommerce\Console\Commands\SyncShopifyCustomersCommand;
use Capell\ShopifyCommerce\Console\Commands\SyncShopifyProductsCommand;
use Capell\ShopifyCommerce\Filament\Pages\ShopifyConnectionPage;
use Capell\ShopifyCommerce\Health\ShopifyCommerceHealthCheck;
use Capell\ShopifyCommerce\Manifest\ShopifyCommerceConsoleCommandsContribution;
use Capell\ShopifyCommerce\Manifest\ShopifyCommerceHealthContribution;
use Capell\ShopifyCommerce\Manifest\ShopifyCommerceModelsContribution;
use Capell\ShopifyCommerce\Manifest\ShopifyCommerceRoutesContribution;
use Capell\ShopifyCommerce\Manifest\ShopifyCommerceSettingsContribution;
use Capell\ShopifyCommerce\Manifest\ShopifyConnectionPageContribution;
use Capell\ShopifyCommerce\Manifest\ShopifyOAuthStatePruneScheduleContribution;
use Capell\ShopifyCommerce\Manifest\ShopifyProductSyncScheduleContribution;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyCustomer;
use Capell\ShopifyCommerce\Models\ShopifyOAuthState;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Capell\ShopifyCommerce\Models\ShopifyProductVariant;
use Capell\ShopifyCommerce\Settings\ShopifyCommerceSettings;

it('declares implemented shopify commerce commands settings health routes models and schedules', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');
    $contributes = data_get($manifest, 'contributes');

    throw_unless(is_array($contributes), RuntimeException::class, 'Expected Shopify Commerce manifest contributions.');

    expect(data_get($manifest, 'commands.install'))->toBe('capell-shopify-commerce:install')
        ->and(data_get($manifest, 'commands.pruneOAuthStates'))->toBe('capell-shopify-commerce:prune-oauth-states')
        ->and(data_get($manifest, 'commands.syncCustomers'))->toBe('capell-shopify-commerce:sync-customers')
        ->and(data_get($manifest, 'commands.syncProducts'))->toBe('capell-shopify-commerce:sync')
        ->and(data_get($manifest, 'security.publicSurface.webhookRoutes', []))->toContain('capell-shopify-commerce.webhooks.shopify')
        ->and(data_get($manifest, 'security.publicSurface.throttledRoutes', []))->toContain('capell-shopify-commerce.webhooks.shopify')
        ->and(data_get($manifest, 'contributionTraceability.deferredContributions'))->toBe([]);

    expect($contributes)->toContain([
        'type' => 'admin-page',
        'class' => ShopifyConnectionPageContribution::class,
        'pageClass' => ShopifyConnectionPage::class,
        'labelKey' => 'capell-shopify-commerce::capell-shopify-commerce.navigation.label',
    ])
        ->and($contributes)->toContain([
            'type' => 'route',
            'class' => ShopifyCommerceRoutesContribution::class,
            'routes' => [
                'capell-shopify-commerce.oauth.install',
                'capell-shopify-commerce.oauth.callback',
                'capell-shopify-commerce.webhooks.shopify',
            ],
        ])
        ->and($contributes)->toContain([
            'type' => 'model',
            'class' => ShopifyCommerceModelsContribution::class,
            'modelClasses' => [
                ShopifyConnection::class,
                ShopifyCustomer::class,
                ShopifyOAuthState::class,
                ShopifyProduct::class,
                ShopifyProductVariant::class,
            ],
        ])
        ->and($contributes)->toContain([
            'type' => 'scheduled-job',
            'class' => ShopifyProductSyncScheduleContribution::class,
            'command' => 'capell-shopify-commerce:sync --all',
            'name' => 'capell-shopify-commerce-product-sync',
            'frequency' => 'everyFifteenMinutes',
            'enabledWhen' => 'capell-shopify-commerce.scheduled_sync_enabled',
        ])
        ->and($contributes)->toContain([
            'type' => 'scheduled-job',
            'class' => ShopifyOAuthStatePruneScheduleContribution::class,
            'command' => 'capell-shopify-commerce:prune-oauth-states',
            'name' => 'capell-shopify-commerce-prune-oauth-states',
            'frequency' => 'hourly',
            'enabledWhen' => 'package installed',
        ])
        ->and($contributes)->toContain([
            'type' => 'console-command',
            'class' => ShopifyCommerceConsoleCommandsContribution::class,
            'commands' => [
                'capell-shopify-commerce:install',
                'capell-shopify-commerce:prune-oauth-states',
                'capell-shopify-commerce:sync-customers',
                'capell-shopify-commerce:sync',
            ],
            'commandClasses' => [
                InstallShopifyCommerceCommand::class,
                PruneExpiredShopifyOAuthStatesCommand::class,
                SyncShopifyCustomersCommand::class,
                SyncShopifyProductsCommand::class,
            ],
        ])
        ->and($contributes)->toContain([
            'type' => 'setting',
            'class' => ShopifyCommerceSettingsContribution::class,
            'settingsClasses' => [ShopifyCommerceSettings::class],
            'settingsGroups' => [ShopifyCommerceSettings::group()],
        ])
        ->and($contributes)->toContain([
            'type' => 'health-check',
            'class' => ShopifyCommerceHealthContribution::class,
            'checkClass' => ShopifyCommerceHealthCheck::class,
        ]);

    foreach ($contributes as $contribution) {
        throw_unless(is_array($contribution), RuntimeException::class, 'Expected Shopify Commerce manifest contribution to be an array.');

        $class = $contribution['class'] ?? null;

        expect(is_string($class) ? class_implements($class) : [])->toContain(ExtensionContribution::class);
    }

    expect(class_implements(ShopifyCommerceRoutesContribution::class))->toContain(RegistersExtensionRoute::class)
        ->and(class_implements(ShopifyProductSyncScheduleContribution::class))->toContain(RunsScheduledExtensionJob::class)
        ->and(class_implements(ShopifyOAuthStatePruneScheduleContribution::class))->toContain(RunsScheduledExtensionJob::class)
        ->and(class_implements(ShopifyCommerceSettingsContribution::class))->toContain(RegistersExtensionSetting::class)
        ->and(class_implements(ShopifyCommerceHealthContribution::class))->toContain(ChecksExtensionHealth::class);
});
