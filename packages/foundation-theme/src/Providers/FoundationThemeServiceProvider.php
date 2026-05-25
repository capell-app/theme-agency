<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Providers;

use Capell\Admin\Data\Extensions\ExtensionManagementSurfaceData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Actions\RegisterBlazeOptimizedViewsAction;
use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\PackageTypeEnum;
use Capell\Core\Events\PackageInstalled;
use Capell\Core\Events\PackageUninstalled;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Theme;
use Capell\Core\Support\Assets\VendorAssetConditionRegistry;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Capell\Core\Support\Themes\ThemeChromeRegistry;
use Capell\FoundationTheme\Console\Commands\GenerateTailwindAssetsCommand;
use Capell\FoundationTheme\Console\Commands\SetupCommand;
use Capell\FoundationTheme\Enums\FoundationThemeAssetEnum;
use Capell\FoundationTheme\Filament\Settings\FoundationThemeSettingsSchema;
use Capell\FoundationTheme\Listeners\RunTailwindAssetsOnPackageChange;
use Capell\FoundationTheme\Livewire\Assets\Table\PageAssets;
use Capell\FoundationTheme\Livewire\Block\Pages;
use Capell\FoundationTheme\Settings\FoundationThemeSettings;
use Capell\FoundationTheme\Support\Assets\FoundationThemeAssetContributor;
use Capell\FoundationTheme\Support\Blade\BladeDirectives;
use Capell\FoundationTheme\Support\Interceptors\Themes\FoundationThemeInterceptor;
use Capell\FoundationTheme\Support\Media\CapellUrlGenerator;
use Capell\FoundationTheme\Support\Tailwind\TailwindAssetsGenerator;
use Capell\FoundationTheme\View\Components\Actions as ActionsComponent;
use Capell\FoundationTheme\View\Components\Block\Page\Breadcrumbs as PageBreadcrumbsComponent;
use Capell\FoundationTheme\View\Components\Block\Page\Children as PageChildrenComponent;
use Capell\FoundationTheme\View\Components\Block\Page\Content as PageContentComponent;
use Capell\FoundationTheme\View\Components\Block\Page\Latest as PageLatestComponent;
use Capell\FoundationTheme\View\Components\Block\Page\Siblings as PageSiblingsComponent;
use Capell\FoundationTheme\View\Components\Block\Slot as SlotComponent;
use Capell\FoundationTheme\View\Components\Footer\Index as FooterIndexComponent;
use Capell\FoundationTheme\View\Components\Layout\Index as LayoutIndexComponent;
use Capell\FoundationTheme\View\Components\Media\Svg;
use Capell\Frontend\Contracts\AssetsRegistryInterface;
use Capell\Frontend\Contracts\FrontendAssetContributor;
use Capell\Frontend\Contracts\FrontendComponentRegistryInterface;
use Capell\Frontend\Data\FrontendAssetContextData;
use Capell\Frontend\Data\FrontendAssetData;
use Capell\LayoutBuilder\Enums\FrontendComponentKeyEnum;
use Capell\LayoutBuilder\Support\LayoutAreas\LayoutAreaRegistry;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Override;
use Spatie\LaravelPackageTools\Package;

final class FoundationThemeServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-foundation-theme';

    public static string $packageName = 'capell-app/foundation-theme';

    public static PackageTypeEnum $type = PackageTypeEnum::Theme;

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasCommands([
                GenerateTailwindAssetsCommand::class,
                SetupCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        $this->registerBladeDirectives();
        $this->registerBladeComponents();
        $this->registerLayoutBuilderRendering();
        $this->registerMediaBladeComponents();
        $this->registerBlazeComponents();
        $this->registerPublishCommands();

        if (! $this->isPackageInstalled()) {
            return;
        }

        $this->registerAssets();
        $this->registerTailwindEventListeners();
        $this->registerVendorAssetConditions();
        $this->registerVendorCssJsAssets();
        $this->registerMediaUrlGenerator();
        $this->registerModelInterceptors();
        $this->registerSettingsSchemas();
        $this->registerLayoutAreas();
        $this->registerThemeChromeComponents();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('capell.tailwind.generator', fn (): TailwindAssetsGenerator => new TailwindAssetsGenerator(
            $this->app->make(Filesystem::class),
        ));
        $this->app->scoped(FoundationThemeAssetContributor::class);

        $this->registerVendorNpmDependencies();
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerAssets(): void
    {
        $this->app->tag(FoundationThemeAssetContributor::class, FrontendAssetContributor::TAG);

        if (! $this->app->bound(AssetsRegistryInterface::class)) {
            return;
        }

        $registry = resolve(AssetsRegistryInterface::class);

        foreach (FoundationThemeAssetEnum::cases() as $asset) {
            $registry->registerAsset(
                $asset->getAsset(),
                new FrontendAssetData(component: $asset->getComponent()),
            );
        }
    }

    private function registerBladeDirectives(): void
    {
        BladeDirectives::register();
    }

    private function registerBlazeComponents(): void
    {
        if (config('capell-foundation-theme.blaze.enabled', false) !== true) {
            return;
        }

        if ($this->app->environment('testing')) {
            return;
        }

        RegisterBlazeOptimizedViewsAction::run(__DIR__ . '/../../resources/views/components');
    }

    private function registerTailwindEventListeners(): void
    {
        Event::listen(PackageInstalled::class, [RunTailwindAssetsOnPackageChange::class, 'handleInstalled']);
        Event::listen(PackageUninstalled::class, [RunTailwindAssetsOnPackageChange::class, 'handleUninstalled']);
    }

    private function registerMediaUrlGenerator(): void
    {
        config(['media-library.url_generator' => CapellUrlGenerator::class]);
    }

    private function registerMediaBladeComponents(): void
    {
        Blade::component('capell::media.svg', Svg::class);
    }

    private function registerBladeComponents(): void
    {
        resolve(ViewFactory::class)->prependNamespace('capell', __DIR__ . '/../../resources/views');

        Blade::anonymousComponentPath(__DIR__ . '/../../resources/views/components', 'capell');
        Blade::component(FooterIndexComponent::class, 'capell::footer.index');
        Blade::component(LayoutIndexComponent::class, 'capell::layout.index');
    }

    private function registerSettingsSchemas(): void
    {
        $registry = resolve(SettingsSchemaRegistry::class);
        $registry->registerSettingsClass('foundation_theme', FoundationThemeSettings::class);
        $registry->register('foundation_theme', FoundationThemeSettingsSchema::class);
        CapellAdmin::registerExtensionManagementSurface(ExtensionManagementSurfaceData::settings(
            packageName: self::$packageName,
            label: 'capell-foundation-theme::generic.foundation_theme',
            settingsGroup: 'foundation_theme',
            icon: 'heroicon-o-swatch',
        ));
    }

    private function registerThemeChromeComponents(): void
    {
        $register = function (ThemeChromeRegistry $registry): void {
            $registry->registerHeader('capell::header.index', __('capell-admin::form.foundation_header'));
            $registry->registerFooter('capell::footer', __('capell-admin::form.foundation_footer'));
        };

        $this->app->afterResolving(ThemeChromeRegistry::class, $register);

        if ($this->app->resolved(ThemeChromeRegistry::class)) {
            $register($this->app->make(ThemeChromeRegistry::class));
        }
    }

    private function registerLayoutAreas(): void
    {
        $register = function (LayoutAreaRegistry $registry): void {
            $registry->register('header', __('capell-layout-builder::generic.header_area'));
        };

        $this->app->afterResolving(LayoutAreaRegistry::class, $register);

        if ($this->app->resolved(LayoutAreaRegistry::class)) {
            $register($this->app->make(LayoutAreaRegistry::class));
        }
    }

    private function registerModelInterceptors(): void
    {
        CapellCore::registerModelInterceptor(Theme::class, interceptorClass: FoundationThemeInterceptor::class);
    }

    private function registerVendorNpmDependencies(): void
    {
        $npmDependencies = config('capell-foundation-theme.npm_dependencies', []);

        if (! is_array($npmDependencies)) {
            return;
        }

        foreach ($npmDependencies as $package => $version) {
            if (! is_string($package)) {
                continue;
            }

            if ($package === '') {
                continue;
            }

            if (! is_string($version)) {
                continue;
            }

            if ($version === '') {
                continue;
            }

            CapellCore::registerVendorAsset(
                VendorAssetData::npmDependency($package, $version, self::$packageName),
            );
        }
    }

    private function registerVendorCssJsAssets(): void
    {
        CapellCore::registerVendorAsset(
            VendorAssetData::buildAsset(
                path: 'vendor/capell-foundation-theme',
                file: 'resources/js/capell-frontend.js',
                packageName: self::$packageName,
                condition: 'foundation-theme-runtime',
            ),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/foundation-theme.css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/blocks/foundation-blocks.css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('tippy.js/dist/tippy.css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('swiper/css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('swiper/css/autoplay', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('swiper/css/pagination', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('swiper/css/navigation', self::$packageName),
        );
    }

    private function registerVendorAssetConditions(): void
    {
        resolve(VendorAssetConditionRegistry::class)->register(
            'foundation-theme-runtime',
            fn (FrontendAssetContextData $context): bool => $context->runtime->usesBeacon
                || $context->runtime->usesIslands
                || $context->runtime->usesLivewire
                || ($context->runtime->modules['layout-builder'] ?? false),
        );
    }

    private function registerPublishCommands(): void
    {
        $this->publishes([
            __DIR__ . '/../../publishes/build' => public_path('vendor/capell-foundation-theme'),
        ], 'capell-foundation-theme-assets');
    }

    private function registerLayoutBuilderRendering(): void
    {
        resolve(ViewFactory::class)->addNamespace(
            'capell-foundation-theme',
            __DIR__ . '/../../resources/views',
        );

        resolve(ViewFactory::class)->addNamespace(
            'capell',
            __DIR__ . '/../../resources/views',
        );

        Blade::anonymousComponentPath(__DIR__ . '/../../resources/views/components', 'capell');
        Blade::anonymousComponentPath(__DIR__ . '/../../resources/views/components', 'capell-foundation-theme');
        Blade::componentNamespace('Capell\\FoundationTheme\\View\\Components', 'capell');
        Blade::componentNamespace('Capell\\FoundationTheme\\View\\Components', 'capell-foundation-theme');
        Blade::component(PageBreadcrumbsComponent::class, 'capell::block.page.breadcrumbs');
        Blade::component(ActionsComponent::class, 'capell::actions');
        Blade::component(ActionsComponent::class, 'capell-foundation-theme::actions');
        Blade::component(PageContentComponent::class, 'capell-block-page-content');
        Blade::component(PageContentComponent::class, 'capell::block.page.content');
        Blade::component(SlotComponent::class, 'capell::block.slot');
        Blade::component('capell-foundation-theme::components.block.wrapper', 'capell-layout-builder::widget.wrapper');
        Blade::component(PageChildrenComponent::class, 'capell::block.page.children');
        Blade::component(PageLatestComponent::class, 'capell::block.page.latest');
        Blade::component(PageSiblingsComponent::class, 'capell::block.page.siblings');

        $registerLivewireComponents = function (): void {
            Livewire::component('capell::block.pages', Pages::class);
            Livewire::component('capell-foundation-theme::block.pages', Pages::class);
            Livewire::component('capell-foundation-theme::assets.table.page-assets', PageAssets::class);

            if (! method_exists(Livewire::getFacadeRoot(), 'addNamespace')) {
                return;
            }

            Livewire::addNamespace(
                namespace: 'capell',
                classNamespace: 'Capell\\FoundationTheme\\Livewire',
                viewPath: __DIR__ . '/../../resources/views/livewire',
                classPath: __DIR__ . '/../Livewire',
                classViewPath: __DIR__ . '/../../resources/views/livewire',
            );

            Livewire::addNamespace(
                namespace: 'capell-foundation-theme',
                classNamespace: 'Capell\\FoundationTheme\\Livewire',
                viewPath: __DIR__ . '/../../resources/views/livewire',
                classPath: __DIR__ . '/../Livewire',
                classViewPath: __DIR__ . '/../../resources/views/livewire',
            );

            if (! $this->app->bound('livewire.factory')) {
                return;
            }

            resolve('livewire.factory')->resolveMissingComponent(
                static fn (string $name): ?string => match ($name) {
                    'capell::block.pages' => Pages::class,
                    'capell-foundation-theme::block.pages' => Pages::class,
                    'capell-foundation-theme::assets.table.page-assets' => PageAssets::class,
                    default => null,
                },
            );
        };

        if ($this->app->isBooted()) {
            $registerLivewireComponents();
        } else {
            $this->app->booted($registerLivewireComponents);
        }

        $this->callAfterResolving(FrontendComponentRegistryInterface::class, function (FrontendComponentRegistryInterface $registry): void {
            $registry
                ->register(
                    key: FrontendComponentKeyEnum::SectionBlock->value,
                    component: 'capell::section.block',
                    props: [
                        'asset',
                        'class',
                        'color',
                        'icon',
                        'image',
                        'linkText',
                        'loop',
                        'meta',
                        'size',
                        'summary',
                        'tags',
                        'title',
                        'url',
                    ],
                )
                ->register(
                    key: FrontendComponentKeyEnum::SectionTeamMember->value,
                    component: 'capell::section.team-member',
                    props: [
                        'asset',
                        'class',
                        'color',
                        'icon',
                        'image',
                        'linkText',
                        'loop',
                        'meta',
                        'size',
                        'summary',
                        'title',
                        'url',
                    ],
                );
        });
    }
}
