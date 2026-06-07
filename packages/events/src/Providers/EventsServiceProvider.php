<?php

declare(strict_types=1);

namespace Capell\Events\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Enums\ResourceEnum as AdminResourceEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Actions\RegisterBlazeOptimizedViewsAction;
use Capell\Core\Data\PageTypeData;
use Capell\Core\Data\VendorAssetData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\CustomerPortal\Contracts\PortalSelfServiceItemProvider;
use Capell\CustomerPortal\Support\PortalSelfServiceItemRegistry;
use Capell\Events\Actions\ProcessDueEventNotificationLogsAction;
use Capell\Events\Actions\ReconcileEventWaitlistsAction;
use Capell\Events\Console\Commands\EventsDoctorCommand;
use Capell\Events\Console\Commands\InstallCommand;
use Capell\Events\Enums\LivewireComponentEnum;
use Capell\Events\Enums\ResourceEnum;
use Capell\Events\Events\EventRegistrationCancelled;
use Capell\Events\Filament\Pages\EventCalendarPage;
use Capell\Events\Listeners\PromoteWaitlistAfterRegistrationCancelled;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventRegistration;
use Capell\Events\Models\EventVenue;
use Capell\Events\Policies\EventOccurrencePolicy;
use Capell\Events\Policies\EventPolicy;
use Capell\Events\Policies\EventRegistrationPolicy;
use Capell\Events\Policies\EventVenuePolicy;
use Capell\Events\Support\CustomerPortal\EventsPortalSelfServiceItemProvider;
use Capell\Events\Support\EditorialCalendar\EventsEditorialCalendarEventContributor;
use Capell\Events\Support\EventModelRegistrar;
use Capell\Events\Support\PublicUrls\EventsPublicUrlContributor;
use Capell\Events\Support\RenderHooks\RegisterEventSchemaHooks;
use Capell\Events\Support\Schema\EventSchemaTemplate;
use Capell\Frontend\Support\Render\RenderHookRegistry;
use Capell\PublishingStudio\Contracts\EditorialCalendarEventContributor;
use Capell\PublishingStudio\WorkspaceRegistry;
use Capell\SeoSuite\Enums\SchemaTemplateTypeEnum;
use Capell\SeoSuite\Support\SchemaTemplates\SchemaTemplateRegistry;
use Capell\SiteDiscovery\Contracts\PublicUrlContributor;
use Composer\InstalledVersions;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Override;
use Spatie\LaravelPackageTools\Package;

class EventsServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-events';

    public static string $packageName = 'capell-app/events';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile()
            ->hasViews(self::$name)
            ->hasTranslations()
            ->hasCommands([
                EventsDoctorCommand::class,
                InstallCommand::class,
            ])
            ->hasMigrations([
                '2026_05_10_190848_01_create_event_venues_table',
                '2026_05_10_190848_02_create_events_table',
                '2026_05_10_190848_03_create_event_occurrences_table',
                '2026_05_10_190848_04_create_event_registrations_table',
                '2026_05_10_190848_05_create_event_notification_logs_table',
                '2026_05_31_070000_06_add_unique_event_notification_logs_identity_index',
            ]);
    }

    public function registeringPackage(): void
    {
        $this->app->booting(function (): void {
            if ($this->isPackageInstalled()) {
                $this->registerAdminResources();
            }
        });

        $this->app->booted(function (): void {
            if (! $this->isPackageInstalled()) {
                return;
            }

            $this->bootInstalledPackage();
        });
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::getPackage(static::$packageName)->isInstalled();
    }

    #[Override]
    protected function isLivewireV3(): bool
    {
        if (! class_exists(InstalledVersions::class) || ! InstalledVersions::isInstalled('livewire/livewire')) {
            return true;
        }

        $version = InstalledVersions::getVersion('livewire/livewire');

        if (! is_string($version)) {
            return true;
        }

        return version_compare($version, '4.0.0', '<');
    }

    private function bootInstalledPackage(): self
    {
        return $this
            ->registerModels()
            ->registerPolicies()
            ->registerAdminResources()
            ->registerPageTypes()
            ->registerPackageAssets()
            ->registerBladeComponents()
            ->registerBlazeComponents()
            ->registerLivewireComponents()
            ->registerRoutes()
            ->registerRenderHooks()
            ->registerEventListeners()
            ->registerSchedule()
            ->registerSeoSchemaTemplate()
            ->registerPublicUrlContributors()
            ->registerEditorialCalendarContributors()
            ->registerCustomerPortalIntegrations()
            ->registerPublishingStudio()
            ->registerAboutCommand();
    }

    private function getVersion(): string
    {
        if (! class_exists(InstalledVersions::class)) {
            return 'dev';
        }

        if (! InstalledVersions::isInstalled(static::$packageName)) {
            return 'dev';
        }

        return InstalledVersions::getPrettyVersion(static::$packageName) ?? 'dev';
    }

    private function registerModels(): self
    {
        EventModelRegistrar::register();

        return $this;
    }

    private function registerPolicies(): self
    {
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(EventVenue::class, EventVenuePolicy::class);
        Gate::policy(EventOccurrence::class, EventOccurrencePolicy::class);
        Gate::policy(EventRegistration::class, EventRegistrationPolicy::class);

        return $this;
    }

    private function registerAdminResources(): self
    {
        CapellAdmin::registerExtensionPage(static::$packageName, EventCalendarPage::class);

        foreach (ResourceEnum::cases() as $resourceEnum) {
            CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::resource(
                class: $resourceEnum->value,
                group: $resourceEnum === ResourceEnum::Event ? AdminResourceEnum::Page->name : $resourceEnum->name,
                name: $resourceEnum === ResourceEnum::Event ? strtolower($resourceEnum->name) : 'default',
            ));
        }

        return $this;
    }

    private function registerPageTypes(): self
    {
        CapellCore::registerPageType(
            new PageTypeData(
                name: 'event',
                model: Event::class,
                label: fn (): string => __('capell-events::generic.event'),
            ),
        );

        return $this;
    }

    private function registerPackageAssets(): self
    {
        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', static::$packageName),
        );

        return $this;
    }

    private function registerBladeComponents(): self
    {
        Blade::componentNamespace('Capell\\Events\\View\\Components', 'capell-events');
        Blade::anonymousComponentNamespace('Capell\\Events\\View\\Components');

        return $this;
    }

    private function registerBlazeComponents(): self
    {
        RegisterBlazeOptimizedViewsAction::run(__DIR__ . '/../../resources/views/livewire');

        return $this;
    }

    private function registerLivewireComponents(): self
    {
        if ($this->isLivewireV3()) {
            foreach (LivewireComponentEnum::getComponents() as $name => $component) {
                if (! class_exists($component)) {
                    continue;
                }

                Livewire::component($name, $component);
            }
        } else {
            Livewire::addNamespace(
                namespace: 'capell-events',
                classNamespace: 'Capell\\Events\\Livewire',
                classPath: __DIR__ . '/../Livewire',
                classViewPath: __DIR__ . '/../../resources/views/livewire',
            );
        }

        return $this;
    }

    private function registerRoutes(): self
    {
        Route::middleware(['web', 'frontend.resolve'])
            ->name('capell-events.')
            ->group(__DIR__ . '/../../routes/web.php');

        return $this;
    }

    private function registerRenderHooks(): self
    {
        if (class_exists(RenderHookRegistry::class)) {
            $this->app->make(RegisterEventSchemaHooks::class)->register();
        }

        return $this;
    }

    private function registerEventListeners(): self
    {
        EventFacade::listen(EventRegistrationCancelled::class, PromoteWaitlistAfterRegistrationCancelled::class);

        return $this;
    }

    private function registerSchedule(): self
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->call(fn (): int => ProcessDueEventNotificationLogsAction::run())
                ->name('capell-events:process-notifications')
                ->everyMinute()
                ->withoutOverlapping()
                ->onOneServer();

            $schedule->call(fn (): int => ReconcileEventWaitlistsAction::run())
                ->name('capell-events:reconcile-waitlists')
                ->everyFifteenMinutes()
                ->withoutOverlapping()
                ->onOneServer();
        });

        return $this;
    }

    private function registerSeoSchemaTemplate(): self
    {
        if (! class_exists(SchemaTemplateRegistry::class)) {
            return $this;
        }

        if (! class_exists(SchemaTemplateTypeEnum::class)) {
            return $this;
        }

        $registry = $this->app->make(SchemaTemplateRegistry::class);
        $registry->registerIfMissing(
            SchemaTemplateTypeEnum::Event,
            new EventSchemaTemplate,
        );

        return $this;
    }

    private function registerPublicUrlContributors(): self
    {
        if (interface_exists(PublicUrlContributor::class)) {
            $this->app->singleton(EventsPublicUrlContributor::class);
            $this->app->tag([EventsPublicUrlContributor::class], PublicUrlContributor::TAG);
        }

        return $this;
    }

    private function registerEditorialCalendarContributors(): self
    {
        if (interface_exists(EditorialCalendarEventContributor::class)) {
            $this->app->singleton(EventsEditorialCalendarEventContributor::class);
            $this->app->tag([EventsEditorialCalendarEventContributor::class], EditorialCalendarEventContributor::TAG);
        }

        return $this;
    }

    private function registerCustomerPortalIntegrations(): self
    {
        if (! class_exists(PortalSelfServiceItemRegistry::class)
            || ! interface_exists(PortalSelfServiceItemProvider::class)) {
            return $this;
        }

        /** @var object $registry */
        $registry = $this->app->make(PortalSelfServiceItemRegistry::class);

        if (! method_exists($registry, 'register')) {
            return $this;
        }

        $registry->register('events.registrations', EventsPortalSelfServiceItemProvider::class);

        return $this;
    }

    private function registerPublishingStudio(): self
    {
        WorkspaceRegistry::register(Event::class);

        return $this;
    }

    private function registerAboutCommand(): self
    {
        if ($this->app->runningInConsole() && class_exists(AboutCommand::class)) {
            AboutCommand::add('Capell', [
                self::$name => $this->getVersion(...),
            ]);
        }

        return $this;
    }
}
