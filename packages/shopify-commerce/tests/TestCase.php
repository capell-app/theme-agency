<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Tests;

use Capell\Admin\Providers\AdminServiceProvider;
use Capell\Admin\Providers\Filament\AdminPanelProvider;
use Capell\Core\Enums\PackageTypeEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Providers\CapellServiceProvider;
use Capell\ShopifyCommerce\Providers\ShopifyCommerceServiceProvider;
use Capell\Tests\AbstractTestCase;
use Capell\Tests\Support\RegisterLocalPackageManifestsServiceProvider;
use Livewire\LivewireServiceProvider;
use Override;

abstract class TestCase extends AbstractTestCase
{
    #[Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $testbenchCacheDirectory = dirname(__DIR__, 3) . '/vendor/orchestra/testbench-core/laravel/bootstrap/cache';

        if (! is_dir($testbenchCacheDirectory)) {
            mkdir($testbenchCacheDirectory, 0777, true);
        }

        file_put_contents($testbenchCacheDirectory . '/capell-packages.php', '<?php return [];');
    }

    protected function getPackageServiceName(): string
    {
        return 'capell-shopify-commerce';
    }

    #[Override]
    protected function getPackageProviders(mixed $app): array
    {
        $providers = array_filter(
            parent::getPackageProviders($app),
            static fn (string $provider): bool => $provider !== RegisterLocalPackageManifestsServiceProvider::class,
        );

        return [
            ...array_values($providers),
            CapellServiceProvider::class,
            AdminServiceProvider::class,
            AdminPanelProvider::class,
            LivewireServiceProvider::class,
            ShopifyCommerceServiceProvider::class,
        ];
    }

    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        $manifestCacheDirectory = $app->bootstrapPath('cache');

        if (! is_dir($manifestCacheDirectory)) {
            mkdir($manifestCacheDirectory, 0777, true);
        }

        file_put_contents($manifestCacheDirectory . '/capell-packages.php', '<?php return [];');

        parent::getEnvironmentSetUp($app);

        CapellCore::registerPackage(
            ShopifyCommerceServiceProvider::$packageName,
            type: PackageTypeEnum::Plugin,
            serviceProviderClass: ShopifyCommerceServiceProvider::class,
            path: dirname(__DIR__),
        );

        CapellCore::forcePackageInstalled(AdminServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(ShopifyCommerceServiceProvider::$packageName);
    }
}
