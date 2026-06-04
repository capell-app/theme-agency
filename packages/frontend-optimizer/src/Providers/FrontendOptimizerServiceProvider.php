<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Data\Extensions\ExtensionManagementSurfaceData;
use Capell\Admin\Enums\ConfiguratorTypeEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Capell\Frontend\Contracts\FrontendAssetManifestRenderer;
use Capell\Frontend\Events\FrontendContextResolved;
use Capell\Frontend\Support\Cache\CacheInvalidationRegistry;
use Capell\FrontendOptimizer\Actions\RenderProfileAssetsAction;
use Capell\FrontendOptimizer\Contracts\CriticalCssGenerator;
use Capell\FrontendOptimizer\Filament\Configurators\Types\FrontendOptimizerPageTypeConfigurator;
use Capell\FrontendOptimizer\Filament\Settings\FrontendOptimizerSettingsSchema;
use Capell\FrontendOptimizer\Listeners\CaptureCriticalCssPageTypeOptOut;
use Capell\FrontendOptimizer\Models\FrontendRenderProfile;
use Capell\FrontendOptimizer\Settings\FrontendOptimizerSettings;
use Capell\FrontendOptimizer\Support\CapellFrontendAssetManifestRenderer;
use Capell\FrontendOptimizer\Support\CriticalCssSettings;
use Capell\FrontendOptimizer\Support\LayoutAssetRegistry;
use Capell\FrontendOptimizer\Support\PlaywrightCriticalCssGenerator;
use Capell\FrontendOptimizer\Support\WidgetAssetRegistry;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Override;
use Spatie\LaravelPackageTools\Package;

final class FrontendOptimizerServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-frontend-optimizer';

    public static string $packageName = 'capell-app/frontend-optimizer';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile('capell-frontend-optimizer')
            ->hasTranslations()
            ->hasMigration('2026_05_10_190851_01_create_frontend_optimizer_tables');
    }

    public function registeringPackage(): void
    {
        parent::registeringPackage();

        $this->app->singleton(LayoutAssetRegistry::class);
        $this->app->singleton(WidgetAssetRegistry::class);
        $this->app->singleton(CriticalCssSettings::class);
        $this->app->singleton(CriticalCssGenerator::class, PlaywrightCriticalCssGenerator::class);

        Blade::directive('frontendOptimizerAssets', fn (string $expression): string => sprintf('<?php echo ' . RenderProfileAssetsAction::class . '::run(%s); ?>', $expression));

        Event::listen(FrontendContextResolved::class, CaptureCriticalCssPageTypeOptOut::class);

    }

    public function packageRegistered(): void
    {
        $this->app->booted(function (): void {
            if (! $this->isPackageInstalled()) {
                return;
            }

            $this->app->singleton(FrontendAssetManifestRenderer::class, CapellFrontendAssetManifestRenderer::class);
            $this->registerSettings();
            $this->registerAdminSurface();
            $this->registerCacheInvalidationDependencies();
        });
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerSettings(): void
    {
        /** @var SettingsSchemaRegistry $registry */
        $registry = $this->app->make(SettingsSchemaRegistry::class);

        $registry->registerSettingsClass(FrontendOptimizerSettings::group(), FrontendOptimizerSettings::class);
        $registry->register(FrontendOptimizerSettings::group(), FrontendOptimizerSettingsSchema::class);
        CapellAdmin::registerExtensionManagementSurface(ExtensionManagementSurfaceData::settings(
            packageName: self::$packageName,
            label: 'capell-frontend-optimizer::settings.critical_css',
            settingsGroup: FrontendOptimizerSettings::group(),
            icon: 'heroicon-o-bolt',
        ));
    }

    private function registerAdminSurface(): void
    {
        CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::configurator(
            class: FrontendOptimizerPageTypeConfigurator::class,
            group: ConfiguratorTypeEnum::Blueprint->value,
            name: FrontendOptimizerPageTypeConfigurator::getKey(),
        ));
    }

    private function registerCacheInvalidationDependencies(): void
    {
        if (! $this->app->bound(CacheInvalidationRegistry::class)) {
            return;
        }

        $registry = $this->app->make(CacheInvalidationRegistry::class);
        $registry->registerDependency(FrontendRenderProfile::class, 'frontend-optimizer-*');
    }
}
