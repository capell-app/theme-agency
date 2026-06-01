<?php

declare(strict_types=1);

namespace Capell\Tests;

use Aimeos\Nestedset\NestedSetServiceProvider;
use AmidEsfahani\FilamentTinyEditor\TinyeditorServiceProvider;
use Awcodes\BadgeableColumn\BadgeableColumnServiceProvider;
use BezhanSalleh\FilamentShield\FilamentShieldServiceProvider;
use BezhanSalleh\FilamentShield\Support\Utils;
use Bkwld\Cloner\ServiceProvider as ClonerServiceProvider;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Capell\Address\Models\Address;
use Capell\Address\Models\Country;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Support\CapellAdminManager;
use Capell\Blog\Models\Article;
use Capell\ContentSections\Models\Section;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Providers\CapellServiceProvider;
use Capell\Core\Support\CapellCoreManager;
use Capell\FoundationTheme\View\Components\Block\Page\Breadcrumbs;
use Capell\FoundationTheme\View\Components\Block\Page\Children;
use Capell\FoundationTheme\View\Components\Block\Page\Content;
use Capell\FoundationTheme\View\Components\Block\Page\Latest;
use Capell\FoundationTheme\View\Components\Block\Page\Siblings;
use Capell\LayoutBuilder\Livewire\Filament\LayoutBuilder;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Models\WidgetAsset;
use Capell\Tests\Fixtures\Models\User;
use Capell\Tests\Fixtures\Policies\RolePolicy;
use Capell\Tests\Support\Concerns\BuildsOrderedMigrationWorkspace;
use Capell\Tests\Support\Concerns\RegistersPublishedConfigs;
use Capell\Tests\Support\Concerns\TestingFrontendWithVite;
use Capell\Tests\Support\PackageTestDatabaseGuard;
use Capell\Tests\Support\RegisterLocalPackageManifestsServiceProvider;
use CmsMulti\FilamentClearCache\FilamentClearCacheServiceProvider;
use CodeWithDennis\FilamentSelectTree\FilamentSelectTreeServiceProvider;
use Faker\Provider\Miscellaneous;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\SpatieLaravelSettingsPluginServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Guava\IconPicker\IconPickerServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\Concerns\InteractsWithSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\PendingCommand;
use Illuminate\View\Factory as ViewFactory;
use LaraZeus\SpatieTranslatable\SpatieTranslatableServiceProvider;
use Livewire\Blaze\BlazeServiceProvider;
use Livewire\Livewire;
use Lorisleiva\Actions\ActionServiceProvider;
use MichalOravec\PaginateRoute\PaginateRouteServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;
use Orchestra\Workbench\WorkbenchServiceProvider;
use Override;
use RuntimeException;
use Saade\FilamentAdjacencyList\FilamentAdjacencyListServiceProvider;
use Sinnbeck\DomAssertions\DomAssertionsServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\ImageOptimizer\Optimizers\Svgo;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\LaravelSettings\LaravelSettingsServiceProvider;
use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionServiceProvider;
use Spatie\Tags\TagsServiceProvider;
use StijnVanouplines\BladeCountryFlags\BladeCountryFlagsServiceProvider;
use STS\FilamentImpersonate\FilamentImpersonateServiceProvider;
use Tapp\FilamentAuthenticationLog\FilamentAuthenticationLogServiceProvider;

abstract class AbstractTestCase extends TestCase
{
    use BuildsOrderedMigrationWorkspace;
    use InteractsWithSession;

    // Add-on bridge compatibility tests need fresh facade/container state between cases.
    use RefreshDatabase;
    use RegistersPublishedConfigs;
    use WithFaker;
    use WithWorkbench;

    protected function setUp(): void
    {
        PackageTestDatabaseGuard::assertEnvironmentIsSafe();

        CapellAdmin::clearResolvedInstance(CapellAdminManager::class);
        CapellCore::clearResolvedInstance(CapellCoreManager::class);

        if (getenv('TEST_TOKEN')) {
            putenv('VIEW_COMPILED_PATH=storage/framework/views/phpunit-' . $this->getPackageServiceName() . '-parallel-' . getenv('TEST_TOKEN'));
        }

        parent::setUp();

        $application = $this->app;

        if ($application !== null) {
            PackageTestDatabaseGuard::assertConfigurationIsSafe($application);
        }

        $this->faker->addProvider(new Miscellaneous($this->faker));

        if (! in_array(TestingFrontendWithVite::class, class_uses_recursive(static::class), true)) {
            $this->withoutVite();
        }

        Config::set('media-library.image_optimizers', [
            Svgo::class => [],
        ]);

        $this->loadMigrationsFrom($this->orderedMigrationWorkspacePath());

        // Temp fix to ensure components are locatable when run in parallel
        resolve(ViewFactory::class)->addNamespace('capell-foundation-theme', __DIR__ . '/../packages/foundation-theme/resources/views');
        resolve(ViewFactory::class)->addNamespace('capell-layout-builder', __DIR__ . '/../packages/foundation-theme/resources/views');
        resolve(ViewFactory::class)->addNamespace('capell', __DIR__ . '/../packages/foundation-theme/resources/views');

        Blade::componentNamespace('Capell\\Blog\\View\\Components', 'capell-blog');
        Blade::componentNamespace('Capell\\FoundationTheme\\View\\Components', 'capell-foundation-theme');
        Blade::componentNamespace('Capell\\FoundationTheme\\View\\Components', 'capell-layout-builder');
        Blade::component(Breadcrumbs::class, 'capell::element.page.breadcrumbs');
        Blade::component(Breadcrumbs::class, 'capell::block.page.breadcrumbs');
        Blade::component(Breadcrumbs::class, 'capell::widget.page.breadcrumbs');
        Blade::component('capell-foundation-theme::components.block.page.breadcrumbs', 'capell-layout-builder-widget-page-breadcrumbs');
        Blade::component(Content::class, 'capell-element-page-content');
        Blade::component(Content::class, 'capell-block-page-content');
        Blade::component(Content::class, 'capell-layout-builder-widget-page-content');
        Blade::component('capell-foundation-theme::components.block.slot', 'capell-layout-builder-widget-slot');
        Blade::component('capell-foundation-theme::components.block.slot', 'capell::widget.slot');
        Blade::component('capell-foundation-theme::components.block.wrapper', 'capell-layout-builder::widget.wrapper');
        Blade::component(Children::class, 'capell::element.page.children');
        Blade::component(Children::class, 'capell::block.page.children');
        Blade::component(Children::class, 'capell::widget.page.children');
        Blade::component(Content::class, 'capell::element.page.content');
        Blade::component(Content::class, 'capell::block.page.content');
        Blade::component(Content::class, 'capell::widget.page.content');
        Blade::component(Latest::class, 'capell::element.page.latest');
        Blade::component(Latest::class, 'capell::block.page.latest');
        Blade::component(Latest::class, 'capell::widget.page.latest');
        Blade::component(Siblings::class, 'capell::element.page.siblings');
        Blade::component(Siblings::class, 'capell::block.page.siblings');
        Blade::component(Siblings::class, 'capell::widget.page.siblings');
        Blade::component(Children::class, 'capell-layout-builder::element.page.children');
        Blade::component(Children::class, 'capell-layout-builder::block.page.children');
        Blade::component(Children::class, 'capell-layout-builder::widget.page.children');
        Blade::component(Content::class, 'capell-layout-builder::element.page.content');
        Blade::component(Content::class, 'capell-layout-builder::block.page.content');
        Blade::component(Content::class, 'capell-layout-builder::widget.page.content');
        Blade::component(Latest::class, 'capell-layout-builder::element.page.latest');
        Blade::component(Latest::class, 'capell-layout-builder::block.page.latest');
        Blade::component(Latest::class, 'capell-layout-builder::widget.page.latest');
        Blade::component(Siblings::class, 'capell-layout-builder::element.page.siblings');
        Blade::component(Siblings::class, 'capell-layout-builder::block.page.siblings');
        Blade::component(Siblings::class, 'capell-layout-builder::widget.page.siblings');
        Livewire::component('capell-layout-builder::filament.layout-builder', LayoutBuilder::class);

        Http::preventStrayRequests();

        Relation::morphMap([
            'address' => Address::class,
            'article' => Article::class,
            'country' => Country::class,
            'section' => Section::class,
            'user' => User::class,
            'block' => Widget::class,
            'block_asset' => WidgetAsset::class,
        ]);

        Model::shouldBeStrict();

        // $this->app->setLocale('en_GB');

        $this->setUpDatabase();
    }

    #[Override]
    protected function tearDown(): void
    {
        try {
            $this->cleanupOrderedMigrationWorkspace();
            Model::clearBootedModels();
        } finally {
            parent::tearDown();
        }
    }

    abstract protected function getPackageServiceName(): string;

    /**
     * @param  array<string, mixed>  $parameters
     */
    #[Override]
    public function artisan($command, $parameters = []): PendingCommand
    {
        $pendingCommand = parent::artisan($command, $parameters);

        throw_unless($pendingCommand instanceof PendingCommand, RuntimeException::class, 'Capell package tests expect console output mocking to remain enabled.');

        return $pendingCommand;
    }

    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp(mixed $app): void
    {
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('database.connections.sqlite.url');

        $this->registerPackageConfigs($app);

        Gate::policy(Utils::getRoleModel(), RolePolicy::class);
    }

    /**
     * Set up the database.
     */
    protected function setUpDatabase(): void
    {
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
    }

    /**
     * @param  Application  $app
     * @return class-string[]
     */
    protected function getPackageProviders(mixed $app): array
    {
        return [
            RegisterLocalPackageManifestsServiceProvider::class,
            WorkbenchServiceProvider::class,
            ActionServiceProvider::class,
            ActionsServiceProvider::class,
            BadgeableColumnServiceProvider::class,
            BladeCountryFlagsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            ClonerServiceProvider::class,
            SpatieTranslatableServiceProvider::class,
            SpatieLaravelSettingsPluginServiceProvider::class,
            TinyeditorServiceProvider::class,
            FilamentServiceProvider::class,
            SupportServiceProvider::class,
            InfolistsServiceProvider::class,
            FilamentAuthenticationLogServiceProvider::class,
            FilamentServiceProvider::class,
            FilamentAdjacencyListServiceProvider::class,
            FilamentImpersonateServiceProvider::class,
            FilamentShieldServiceProvider::class,
            FilamentSelectTreeServiceProvider::class,
            FilamentClearCacheServiceProvider::class,
            FormsServiceProvider::class,
            PaginateRouteServiceProvider::class,
            ActivitylogServiceProvider::class,
            LaravelDataServiceProvider::class,
            NestedSetServiceProvider::class,
            PermissionServiceProvider::class,
            IconPickerServiceProvider::class,
            DomAssertionsServiceProvider::class,
            SpatieLaravelSettingsPluginServiceProvider::class,
            CapellServiceProvider::class,
            BlazeServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            LaravelSettingsServiceProvider::class,
            SchemasServiceProvider::class,
            CapellServiceProvider::class,
            LaravelSettingsServiceProvider::class,
            TablesServiceProvider::class,
            TagsServiceProvider::class,
            MediaLibraryServiceProvider::class,
            WidgetsServiceProvider::class,
            NotificationsServiceProvider::class,
        ];
    }

    /**
     * @param  array<array-key, mixed>  $packages
     */
    protected function registerPackageConfigs(Application $app, ?array $packages = null): void
    {
        if ($packages === null || $packages === []) {
            $packages = $this->getDefaultPackages();
        }

        $this->registerPublishConfig('core');
        $this->registerPublishConfig('admin');
        $this->registerPublishConfig('frontend');

        foreach ($packages as $package_key => $package) {
            $config = require __DIR__ . '/..' . $this->getPackageFile($package);

            $this->registerPackageConfig($package_key, $config);
        }

        // config('filament-shield.register_role_policy.enabled', false);
        Config::set('filament-shield.authenticable-resources', [User::class]);
        Config::set('filament-shield.auth_provider_model', User::class);
        // Prevent role being assigned to created user
        Config::set('filament-shield.panel_user.enabled', false);

        // Route super_admin bypass through Gate::before so tests don't need
        // every Shield-generated permission seeded. With define_via_gate=false
        // (the package default) Shield expects `shield:generate` to have run
        // and the role to have been granted every permission — tests don't
        // run that pipeline, so any `hasPermissionTo('ViewAny:Layout')`-style
        // check from a registered policy throws PermissionDoesNotExist.
        Config::set('filament-shield.super_admin.define_via_gate', true);

        Config::set('auth.providers.users.model', User::class);

        $pageCacheDirectory = getenv('TEST_TOKEN')
            ? 'page-cache-' . getenv('TEST_TOKEN')
            : 'page-cache';

        Config::set('filesystems.disks.page_cache', [
            'driver' => 'local',
            'root' => public_path($pageCacheDirectory),
            'throw' => false,
        ]);

        if (getenv('TEST_TOKEN')) {
            Config::set('settings.cache.prefix', 'settings-cache-' . getenv('TEST_TOKEN'));
        }
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function getDefaultPackages(): array
    {
        return [
            'filament-shield' => [
                'user' => 'bezhansalleh',
                'name' => 'filament-shield',
                'file' => 'filament-shield',
            ],
            'login-audit' => [
                'user' => 'rappasoft',
                'name' => 'laravel-authentication-log',
                'file' => 'authentication-log',
            ],
            'permission' => [
                'user' => 'spatie',
                'name' => 'laravel-permission',
                'file' => 'permission',
            ],
            'settings' => [
                'user' => 'spatie',
                'name' => 'laravel-settings',
                'file' => 'settings',
            ],
        ];
    }

    protected function registerPublishConfig(string $package): void
    {
        $configs = $this->getPublishConfigs($package);

        foreach ($configs as $configFile) {
            $config = require $configFile;
            $configName = basename((string) $configFile, '.php');

            $this->registerPackageConfig($configName, $config);
        }
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function getPublishConfigs(string $package): array
    {
        $path = realpath(__DIR__ . '/../packages/' . $package . '/publishes/config');

        if (in_array($path, ['', '0', false], true)) {
            return [];
        }

        $configs = glob($path . '/*.php');

        return $configs === false ? [] : $configs;
    }

    /**
     * @param  array<array-key, mixed>  $migrations
     */
    protected function registerAndMigrateSettings(array $migrations, string $basePath): void
    {
        $settingsMigrator = resolve(SettingsMigrator::class);
        foreach ($migrations as $migrationFile) {
            $path = sprintf('%s/%s.php', $basePath, $migrationFile);
            /** @var SettingsMigration $migration */
            $migration = require $path;
            if (method_exists($migration, 'setMigrationAssistant')) {
                $migration->setMigrationAssistant($settingsMigrator);
            }

            if (! property_exists($migration, 'migration')) {
                // @phpstan-ignore property.notFound
                $migration->migration = $settingsMigrator;
            }

            $migration->up();
        }
    }

    /**
     * @param  array<array-key, mixed>  $package
     */
    private function getPackageFile(array $package): string
    {
        $path = '/vendor/' . basename((string) $package['user']) . '/' . basename((string) $package['name']) . '/config';
        $file = basename((string) $package['file']) . '.php';

        return sprintf('%s/%s', $path, $file);
    }

    /**
     * @param  array<array-key, mixed>  $config
     */
    private function registerPackageConfig(string $package, array $config): void
    {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $this->registerPackageConfig(sprintf('%s.%s', $package, $key), $value);

                continue;
            }

            config()->set(sprintf('%s.%s', $package, $key), $value);
        }
    }
}
