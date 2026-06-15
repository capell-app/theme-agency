<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;
use Capell\Core\Contracts\Extensions\RegistersExtensionFrontendComponent;
use Capell\Core\Contracts\Extensions\RegistersExtensionPageType;
use Capell\Core\Contracts\Extensions\RegistersExtensionRenderHook;
use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\Core\Contracts\Extensions\RegistersExtensionWidget;
use Capell\Core\Contracts\Extensions\RunsExtensionMigration;
use Capell\Core\Contracts\Extensions\RunsScheduledExtensionJob;
use Capell\Core\Support\Manifest\ManifestValidator;
use Capell\Events\Actions\ProcessDueEventNotificationLogsAction;
use Capell\Events\Actions\ReconcileEventWaitlistsAction;
use Capell\Events\Console\Commands\EventsDoctorCommand;
use Capell\Events\Console\Commands\InstallCommand;
use Capell\Events\Filament\Pages\EventCalendarPage;
use Capell\Events\Filament\Resources\Events\EventResource;
use Capell\Events\Filament\Resources\Occurrences\EventOccurrenceResource;
use Capell\Events\Filament\Resources\Registrations\EventRegistrationResource;
use Capell\Events\Filament\Resources\Venues\EventVenueResource;
use Capell\Events\Filament\Widgets\EventCalendarWidget;
use Capell\Events\Health\EventsHealthCheck;
use Capell\Events\Livewire\EventCalendar;
use Capell\Events\Livewire\Page\EventsCalendarPage;
use Capell\Events\Livewire\Page\EventsListingPage;
use Capell\Events\Manifest\EventsAdminPageContribution;
use Capell\Events\Manifest\EventsAdminResourcesContribution;
use Capell\Events\Manifest\EventsConsoleCommandsContribution;
use Capell\Events\Manifest\EventsDashboardWidgetsContribution;
use Capell\Events\Manifest\EventsFrontendComponentsContribution;
use Capell\Events\Manifest\EventsMigrationsContribution;
use Capell\Events\Manifest\EventsModelsContribution;
use Capell\Events\Manifest\EventsPageTypesContribution;
use Capell\Events\Manifest\EventsRenderHookContribution;
use Capell\Events\Manifest\EventsRoutesContribution;
use Capell\Events\Manifest\EventsScheduleContribution;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventNotificationLog;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventRegistration;
use Capell\Events\Models\EventVenue;
use Capell\Events\Providers\EventsServiceProvider;
use Capell\Events\Support\RenderHooks\RegisterEventSchemaHooks;

it('declares shipped events package contribution surfaces', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = capell_json_file_array($packagePath . '/capell.json');
    $composer = capell_json_file_array($packagePath . '/composer.json');
    $contributions = data_get($manifest, 'contributes');

    throw_unless(is_array($contributions), RuntimeException::class, 'Expected Events manifest contributions.');

    (new ManifestValidator)->validate($manifest, $composer, 'capell-app/events', $packagePath . '/capell.json');

    expect($manifest)
        ->toHaveKey('manifest-version', 3)
        ->toHaveKey('name', 'capell-app/events')
        ->toHaveKey('namespace', 'Capell\\Events')
        ->and(data_get($manifest, 'providers.runtime', []))->toContain(EventsServiceProvider::class)
        ->and(data_get($manifest, 'security.publicSurface.routeNames'))->toBe([
            'capell-events.calendar-feed',
            'capell-events.listing-calendar-feed',
        ])
        ->and(collect($contributions))->toContain(
            [
                'type' => 'admin-page',
                'class' => EventsAdminPageContribution::class,
                'pageClass' => EventCalendarPage::class,
                'navigationGroup' => 'content',
                'label' => 'Events admin calendar page.',
            ],
            [
                'type' => 'admin-resource',
                'class' => EventsAdminResourcesContribution::class,
                'resourceClasses' => [
                    EventResource::class,
                    EventVenueResource::class,
                    EventOccurrenceResource::class,
                    EventRegistrationResource::class,
                ],
                'groups' => ['Page', 'EventVenue', 'EventOccurrence', 'EventRegistration'],
            ],
            [
                'type' => 'dashboard-widget',
                'class' => EventsDashboardWidgetsContribution::class,
                'widgetClass' => EventCalendarWidget::class,
            ],
            [
                'type' => 'model',
                'class' => EventsModelsContribution::class,
                'modelClasses' => [
                    Event::class,
                    EventVenue::class,
                    EventOccurrence::class,
                    EventRegistration::class,
                    EventNotificationLog::class,
                ],
            ],
            [
                'type' => 'page-type',
                'class' => EventsPageTypesContribution::class,
                'name' => 'event',
                'modelClass' => Event::class,
            ],
            [
                'type' => 'page-variation',
                'class' => EventsPageTypesContribution::class,
                'name' => 'event',
                'modelClass' => Event::class,
                'resourceName' => 'event',
            ],
            [
                'type' => 'frontend-component',
                'class' => EventsFrontendComponentsContribution::class,
                'componentClasses' => [
                    EventCalendar::class,
                    EventsCalendarPage::class,
                    EventsListingPage::class,
                ],
                'keys' => [
                    'capell-events::event-calendar',
                    'capell-events::page.events-calendar',
                    'capell-events::page.events-listing',
                ],
            ],
            [
                'type' => 'route',
                'class' => EventsRoutesContribution::class,
                'routeNames' => [
                    'capell-events.calendar-feed',
                    'capell-events.listing-calendar-feed',
                ],
                'middleware' => ['web', 'frontend.resolve'],
            ],
            [
                'type' => 'render-hook',
                'class' => EventsRenderHookContribution::class,
                'hookClass' => RegisterEventSchemaHooks::class,
                'location' => 'headClose',
                'keys' => ['event-schema'],
                'cacheSafe' => false,
            ],
            [
                'type' => 'migration',
                'class' => EventsMigrationsContribution::class,
                'tables' => [
                    'event_venues',
                    'events',
                    'event_occurrences',
                    'event_registrations',
                    'event_notification_logs',
                ],
            ],
            [
                'type' => 'scheduled-job',
                'class' => EventsScheduleContribution::class,
                'name' => 'capell-events:process-notifications',
                'actionClass' => ProcessDueEventNotificationLogsAction::class,
                'frequency' => 'everyMinute',
            ],
            [
                'type' => 'scheduled-job',
                'class' => EventsScheduleContribution::class,
                'name' => 'capell-events:reconcile-waitlists',
                'actionClass' => ReconcileEventWaitlistsAction::class,
                'frequency' => 'everyFifteenMinutes',
            ],
            [
                'type' => 'console-command',
                'class' => EventsConsoleCommandsContribution::class,
                'commands' => [
                    'capell:events-install',
                    'capell:events-doctor',
                ],
                'commandClasses' => [
                    InstallCommand::class,
                    EventsDoctorCommand::class,
                ],
            ],
            [
                'type' => 'health-check',
                'class' => EventsHealthCheck::class,
            ],
        )
        ->and(data_get($manifest, 'contributionTraceability.deferredContributions'))->toBe([]);
});

it('uses contribution contracts that match the manifest contribution types', function (): void {
    expect(class_implements(EventsAdminPageContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(EventsAdminResourcesContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(EventsDashboardWidgetsContribution::class))->toContain(RegistersExtensionWidget::class)
        ->and(class_implements(EventsModelsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(EventsPageTypesContribution::class))->toContain(RegistersExtensionPageType::class)
        ->and(class_implements(EventsFrontendComponentsContribution::class))->toContain(RegistersExtensionFrontendComponent::class)
        ->and(class_implements(EventsRoutesContribution::class))->toContain(RegistersExtensionRoute::class)
        ->and(class_implements(EventsRenderHookContribution::class))->toContain(RegistersExtensionRenderHook::class)
        ->and(class_implements(EventsMigrationsContribution::class))->toContain(RunsExtensionMigration::class)
        ->and(class_implements(EventsScheduleContribution::class))->toContain(RunsScheduledExtensionJob::class)
        ->and(class_implements(EventsConsoleCommandsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(EventsHealthCheck::class))->toContain(ChecksExtensionHealth::class);
});
