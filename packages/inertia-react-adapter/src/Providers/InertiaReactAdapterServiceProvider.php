<?php

declare(strict_types=1);

namespace Capell\InertiaReactAdapter\Providers;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Assets\VendorAssetConditionRegistry;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Inertia\Actions\ResolveInertiaAdapterKeyAction;
use Capell\Inertia\Data\InertiaAdapterData;
use Capell\Inertia\Support\InertiaAdapterRegistry;
use Override;
use Spatie\LaravelPackageTools\Package;

final class InertiaReactAdapterServiceProvider extends AbstractPackageServiceProvider
{
    public const string ADAPTER_KEY = 'react';

    public const string BUILD_PATH = 'vendor/capell/inertia-react';

    public const string ENTRYPOINT = 'resources/js/app.jsx';

    public static string $name = 'capell-inertia-react-adapter';

    public static string $packageName = 'capell-app/inertia-react-adapter';

    public function configurePackage(Package $package): void
    {
        $package->name(self::$name);
    }

    public function packageBooted(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this->registerAdapter();
        $this->registerVendorAssetConditions();
        $this->registerVendorAssets();
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerAdapter(): void
    {
        $configure = fn (InertiaAdapterRegistry $registry): InertiaAdapterRegistry => $registry->register($this->adapterData());

        $this->app->afterResolving(InertiaAdapterRegistry::class, $configure);

        if ($this->app->resolved(InertiaAdapterRegistry::class)) {
            $configure($this->app->make(InertiaAdapterRegistry::class));
        }
    }

    private function registerVendorAssets(): void
    {
        foreach ($this->adapterData()->npmDependencies as $package => $version) {
            CapellCore::registerVendorAsset(VendorAssetData::npmDependency($package, $version, self::$packageName));
        }

        CapellCore::registerVendorAsset(VendorAssetData::buildAsset(
            path: self::BUILD_PATH,
            file: self::ENTRYPOINT,
            packageName: self::$packageName,
            condition: 'capell-inertia-adapter-react',
        ));
    }

    private function registerVendorAssetConditions(): void
    {
        resolve(VendorAssetConditionRegistry::class)->register(
            'capell-inertia-adapter-react',
            fn (mixed $context): bool => ($context->runtime->usesInertia ?? false)
                && ResolveInertiaAdapterKeyAction::run() === self::ADAPTER_KEY,
        );
    }

    private function adapterData(): InertiaAdapterData
    {
        return new InertiaAdapterData(
            key: self::ADAPTER_KEY,
            packageName: self::$packageName,
            npmDependencies: [
                '@inertiajs/react' => '^3.0.0',
                '@vitejs/plugin-react' => '^5.0.0',
                'react' => '^19.0.0',
                'react-dom' => '^19.0.0',
            ],
            buildPath: self::BUILD_PATH,
            entrypoint: self::ENTRYPOINT,
            components: [
                'page' => 'Capell/Page',
                'bookingRequest' => 'Capell/Bookings/Request',
            ],
        );
    }
}
