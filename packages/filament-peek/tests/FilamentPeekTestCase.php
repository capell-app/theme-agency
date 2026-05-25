<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Tests;

use AmidEsfahani\FilamentTinyEditor\TinyeditorServiceProvider;
use Awcodes\BadgeableColumn\BadgeableColumnServiceProvider;
use BezhanSalleh\FilamentShield\FilamentShieldServiceProvider;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Providers\AdminServiceProvider;
use Capell\Admin\Providers\Filament\AdminPanelProvider;
use Capell\ContentBlocks\Providers\ContentBlocksServiceProvider;
use Capell\Core\Facades\CapellCore;
use Capell\FilamentPeek\Providers\FilamentPeekServiceProvider as CapellFilamentPeekServiceProvider;
use Capell\Frontend\Providers\FrontendServiceProvider;
use Capell\LayoutBuilder\LayoutBuilderServiceProvider;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use CmsMulti\FilamentClearCache\FilamentClearCacheServiceProvider;
use CodeWithDennis\FilamentSelectTree\FilamentSelectTreeServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Guava\IconPicker\IconPickerServiceProvider;
use Illuminate\Contracts\Config\Repository;
use LaraZeus\SpatieTranslatable\SpatieTranslatableServiceProvider;
use Livewire\LivewireServiceProvider;
use Override;
use Pboivin\FilamentPeek\FilamentPeekServiceProvider;
use Saade\FilamentAdjacencyList\FilamentAdjacencyListServiceProvider;
use STS\FilamentImpersonate\FilamentImpersonateServiceProvider;
use Tanmuhittin\LaravelGoogleTranslate\LaravelGoogleTranslateServiceProvider;

abstract class FilamentPeekTestCase extends PackagesTestCase
{
    use CreatesAdminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->registerAndMigrateSettings(
            CapellCore::getSettingMigrations(),
            __DIR__ . '/../../../vendor/capell-app/core/database/settings',
        );

        $this->registerAndMigrateSettings(
            CapellAdmin::getSettingMigrations(),
            __DIR__ . '/../../../vendor/capell-app/admin/database/settings',
        );
    }

    #[Override]
    protected function getPackageServiceName(): string
    {
        return 'capell-filament-peek';
    }

    /**
     * @return class-string[]
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array
    {
        return [
            ...parent::getPackageProviders($app),
            ActionsServiceProvider::class,
            BadgeableColumnServiceProvider::class,
            SpatieTranslatableServiceProvider::class,
            TinyeditorServiceProvider::class,
            FilamentServiceProvider::class,
            FilamentAdjacencyListServiceProvider::class,
            FilamentShieldServiceProvider::class,
            FilamentSelectTreeServiceProvider::class,
            FilamentClearCacheServiceProvider::class,
            FilamentPeekServiceProvider::class,
            FilamentImpersonateServiceProvider::class,
            FormsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            IconPickerServiceProvider::class,
            LaravelGoogleTranslateServiceProvider::class,
            SupportServiceProvider::class,
            SchemasServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            NotificationsServiceProvider::class,
            AdminServiceProvider::class,
            ContentBlocksServiceProvider::class,
            AdminPanelProvider::class,
            FrontendServiceProvider::class,
            LayoutBuilderServiceProvider::class,
            LivewireServiceProvider::class,
            CapellFilamentPeekServiceProvider::class,
        ];
    }

    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        parent::getEnvironmentSetUp($app);

        $app->make(Repository::class)->set('cache.default', 'array');
        $app->make(Repository::class)->set('capell-filament-peek.preview.cache_store', 'array');

        CapellCore::forcePackageInstalled(AdminServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(ContentBlocksServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(FrontendServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(LayoutBuilderServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(CapellFilamentPeekServiceProvider::$packageName);
    }
}
