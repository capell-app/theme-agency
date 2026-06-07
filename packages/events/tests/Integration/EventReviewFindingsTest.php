<?php

declare(strict_types=1);

use Capell\Admin\Filament\Resources\Pages\PageResource;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\Core\Models\Theme;
use Capell\Events\Actions\BuildCalendarFeedAction;
use Capell\Events\Actions\BuildEventSchemaAction;
use Capell\Events\Actions\ProcessDueEventNotificationLogsAction;
use Capell\Events\Actions\QueryPublicEventOccurrencesAction;
use Capell\Events\Actions\ReconcileEventWaitlistsAction;
use Capell\Events\Actions\RegisterForEventOccurrenceAction;
use Capell\Events\Actions\ResolvePublicEventSchemaOccurrenceAction;
use Capell\Events\Actions\ScheduleEventNotificationsAction;
use Capell\Events\Actions\SendEventNotificationAction;
use Capell\Events\Actions\UpdateRegistrationStatusAction;
use Capell\Events\Data\EventRegistrationData;
use Capell\Events\Enums\EventBookingModeEnum;
use Capell\Events\Enums\EventNotificationTypeEnum;
use Capell\Events\Enums\EventOccurrenceStatusEnum;
use Capell\Events\Enums\EventRegistrationStatusEnum;
use Capell\Events\Enums\EventVisibilityEnum;
use Capell\Events\Events\EventRegistrationCancelled;
use Capell\Events\Filament\Resources\Events\EventResource;
use Capell\Events\Filament\Resources\Events\Pages\CreateEvent;
use Capell\Events\Filament\Resources\Events\Pages\EditEvent;
use Capell\Events\Filament\Resources\Events\Pages\ListEvents;
use Capell\Events\Http\Controllers\CalendarFeedController;
use Capell\Events\Livewire\EventCalendar;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventNotificationLog;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventRegistration;
use Capell\Events\Notifications\EventRegistrationNotification;
use Capell\Events\Providers\EventsServiceProvider;
use Capell\Frontend\Contracts\FrontendContextReader;
use Capell\Frontend\Support\CapellFrontendContext;
use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Event as ScheduledEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelPackageTools\Package;

it('wires events through the pageable resource flow', function (): void {
    expect(EventResource::class)->toExtend(PageResource::class)
        ->and(EventResource::getPages())->toHaveKeys(['index', 'create', 'edit'])
        ->and(EventResource::getPages()['index']->getPage())->toBe(ListEvents::class)
        ->and(EventResource::getPages()['create']->getPage())->toBe(CreateEvent::class)
        ->and(EventResource::getPages()['edit']->getPage())->toBe(EditEvent::class);
});

it('uses an event page url plus occurrence date for public occurrence urls and feeds', function (): void {
    $event = Event::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-06-10 10:00:00', 'UTC'),
        'visible_from' => CarbonImmutable::parse('2026-01-01 00:00:00', 'UTC'),
    ]);
    $site = $event->site;
    $language = Language::factory()->english()->create();
    SiteDomain::factory()->for($site)->for($language)->default()->create();

    PageUrl::factory()
        ->page($event)
        ->site($site)
        ->language($language)
        ->state(['url' => '/events/community-event'])
        ->create();

    $occurrence = EventOccurrence::factory()->create([
        'event_id' => $event->getKey(),
        'starts_at' => CarbonImmutable::parse('2026-06-10 10:00:00', 'UTC'),
        'occurrence_key' => '20260610T100000',
    ]);

    expect($occurrence->load('event.pageUrl')->occurrenceUrl())->toEndWith('/events/community-event/2026-06-10')
        ->and(BuildCalendarFeedAction::run($site))->toContain('/events/community-event/2026-06-10');
});

it('serves calendar feeds with freshness headers and conditional etag support', function (): void {
    $event = Event::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-06-10 10:00:00', 'UTC'),
        'visible_from' => CarbonImmutable::parse('2026-01-01 00:00:00', 'UTC'),
    ]);
    $site = $event->site;
    $language = Language::factory()->english()->create();
    SiteDomain::factory()->for($site)->for($language)->default()->create();

    EventOccurrence::factory()->create([
        'event_id' => $event->getKey(),
        'starts_at' => CarbonImmutable::parse('2026-06-10 10:00:00', 'UTC'),
        'occurrence_key' => '20260610T100000',
    ]);

    app()->instance(CapellFrontendContext::class, new CapellFrontendContext(new readonly class($site) implements FrontendContextReader
    {
        public function __construct(private Site $site) {}

        public function site(): Site
        {
            return $this->site;
        }

        public function language(): ?Language
        {
            return null;
        }

        public function page(): ?Pageable
        {
            return null;
        }

        public function layout(): ?Layout
        {
            return null;
        }

        public function theme(): ?Theme
        {
            return null;
        }

        public function params(): array
        {
            return [];
        }

        public function slug(): ?string
        {
            return null;
        }

        public function isError(): bool
        {
            return false;
        }

        public function setFrontendData(string $key, mixed $value): self
        {
            return $this;
        }

        public function getFrontendData(?string $key = null): mixed
        {
            return $key === null ? [] : null;
        }
    }));

    $response = (new CalendarFeedController)(Request::create('/events.ics'));

    expect($response->getStatusCode())->toBe(200)
        ->and((string) $response->headers->get('Content-Type'))->toContain('text/calendar; charset=UTF-8')
        ->and((string) $response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=3600')
        ->and($response->headers->get('ETag'))->not->toBeNull();

    $conditionalRequest = Request::create('/events.ics', Symfony\Component\HttpFoundation\Request::METHOD_GET, [], [], [], [
        'HTTP_IF_NONE_MATCH' => (string) $response->headers->get('ETag'),
    ]);

    $conditionalResponse = (new CalendarFeedController)($conditionalRequest);

    expect($conditionalResponse->getStatusCode())->toBe(304);
});

it('excludes stale private unpublished and cancelled occurrences from public queries', function (): void {
    $site = Event::factory()->create()->site;
    $visibleEvent = Event::factory()->for($site)->create([
        'visibility' => EventVisibilityEnum::Public,
        'visible_from' => CarbonImmutable::parse('2026-01-01 00:00:00', 'UTC'),
        'visible_until' => null,
    ]);
    $privateEvent = Event::factory()->for($site)->create(['visibility' => EventVisibilityEnum::Private]);
    $pendingEvent = Event::factory()->for($site)->create(['visible_from' => CarbonImmutable::now()->addDay()]);
    $expiredEvent = Event::factory()->for($site)->create(['visible_until' => CarbonImmutable::now()->subDay()]);

    $publicOccurrence = EventOccurrence::factory()->for($visibleEvent, 'event')->create(['occurrence_key' => '20260610T100000']);
    EventOccurrence::factory()->for($visibleEvent, 'event')->create(['occurrence_key' => '20260611T100000', 'visibility' => EventVisibilityEnum::Private]);
    EventOccurrence::factory()->for($visibleEvent, 'event')->create(['occurrence_key' => '20260612T100000', 'status' => EventOccurrenceStatusEnum::Cancelled]);
    EventOccurrence::factory()->for($privateEvent, 'event')->create(['occurrence_key' => '20260613T100000']);
    EventOccurrence::factory()->for($pendingEvent, 'event')->create(['occurrence_key' => '20260614T100000']);
    EventOccurrence::factory()->for($expiredEvent, 'event')->create(['occurrence_key' => '20260615T100000']);

    $results = QueryPublicEventOccurrencesAction::run(
        $site,
        CarbonImmutable::now()->subMonth(),
        CarbonImmutable::now()->addMonth(),
    );

    expect($results->pluck('id')->all())->toBe([$publicOccurrence->getKey()]);
});

it('keeps events package migration registration order foreign-key safe', function (): void {
    $package = new Package;
    (new EventsServiceProvider(app()))->configurePackage($package);

    expect($package->migrationFileNames[0])->toBe('2026_05_10_190848_01_create_event_venues_table')
        ->and($package->migrationFileNames[1])->toBe('2026_05_10_190848_02_create_events_table');
});

it('does not overbook capacity and rejects overflow when waitlist is disabled', function (): void {
    Notification::fake();

    $occurrence = EventOccurrence::factory()->create([
        'booking_mode' => EventBookingModeEnum::NativeRsvp,
        'capacity' => 1,
        'waitlist_enabled' => false,
    ]);

    RegisterForEventOccurrenceAction::run(
        $occurrence,
        new EventRegistrationData(name: 'Alice Example', email: 'alice@example.com'),
    );

    expect(fn (): mixed => RegisterForEventOccurrenceAction::run(
        $occurrence->refresh(),
        new EventRegistrationData(name: 'Bob Example', email: 'bob@example.com'),
    ))->toThrow(ValidationException::class)
        ->and($occurrence->refresh()->registration_count)->toBe(1);
});

it('does not resend duplicate notification logs that are already queued or sent', function (): void {
    Notification::fake();

    $registration = EventRegistration::factory()->create();

    SendEventNotificationAction::run($registration, EventNotificationTypeEnum::Confirmation);
    SendEventNotificationAction::run($registration, EventNotificationTypeEnum::Confirmation);

    Notification::assertSentTimes(EventRegistrationNotification::class, 1);

    expect(EventNotificationLog::query()
        ->where('event_registration_id', $registration->getKey())
        ->where('type', EventNotificationTypeEnum::Confirmation)
        ->count())->toBe(1);
});

it('schedules registration notifications only after the registration transaction commits', function (): void {
    Notification::fake();

    $occurrence = EventOccurrence::factory()->create([
        'booking_mode' => EventBookingModeEnum::NativeRsvp,
        'capacity' => 10,
        'starts_at' => CarbonImmutable::parse('2026-06-10 10:00:00', 'UTC'),
    ]);

    DB::beginTransaction();

    try {
        $registration = RegisterForEventOccurrenceAction::run(
            $occurrence,
            new EventRegistrationData(name: 'Alice Example', email: 'alice@example.com'),
        );

        expect(EventNotificationLog::query()
            ->where('event_registration_id', $registration->getKey())
            ->count())->toBe(0);

        DB::commit();
    } catch (Throwable $throwable) {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        throw $throwable;
    }

    expect(EventNotificationLog::query()
        ->where('event_registration_id', $registration->getKey())
        ->toBase()
        ->pluck('type')
        ->all())->toContain(
            EventNotificationTypeEnum::Confirmation->value,
            EventNotificationTypeEnum::Reminder->value,
        );

    Notification::assertSentOnDemand(EventRegistrationNotification::class);
});

it('prevents duplicate registration notification log identities at the database layer', function (): void {
    $registration = EventRegistration::factory()->create();

    EventNotificationLog::query()->create([
        'event_occurrence_id' => $registration->event_occurrence_id,
        'event_registration_id' => $registration->getKey(),
        'type' => EventNotificationTypeEnum::Confirmation,
        'recipient_email' => $registration->email,
        'status' => 'queued',
        'scheduled_for' => now(),
    ]);

    expect(fn (): mixed => EventNotificationLog::query()->create([
        'event_occurrence_id' => $registration->event_occurrence_id,
        'event_registration_id' => $registration->getKey(),
        'type' => EventNotificationTypeEnum::Confirmation,
        'recipient_email' => $registration->email,
        'status' => 'queued',
        'scheduled_for' => now(),
    ]))->toThrow(QueryException::class);
});

it('keeps scheduled reminders idempotent for repeated registration scheduling', function (): void {
    Notification::fake();

    $occurrence = EventOccurrence::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-06-10 10:00:00', 'UTC'),
    ]);
    $registration = EventRegistration::factory()->for($occurrence, 'occurrence')->create([
        'email' => 'repeat-attendee@example.com',
    ]);

    ScheduleEventNotificationsAction::run($registration);
    ScheduleEventNotificationsAction::run($registration->refresh());

    expect(EventNotificationLog::query()
        ->where('event_registration_id', $registration->getKey())
        ->where('type', EventNotificationTypeEnum::Reminder)
        ->where('recipient_email', 'repeat-attendee@example.com')
        ->count())->toBe(1);
});

it('schedules configured reminder cadence and honors per-event opt out', function (): void {
    Notification::fake();

    $occurrence = EventOccurrence::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-06-10 10:00:00', 'UTC'),
    ]);
    $occurrence->event->forceFill([
        'notification_settings' => [
            'reminder_offsets_minutes' => [10080, 1440, 60],
        ],
    ])->save();
    $registration = EventRegistration::factory()->for($occurrence, 'occurrence')->create([
        'email' => 'cadence-attendee@example.com',
    ]);

    ScheduleEventNotificationsAction::run($registration);

    expect(EventNotificationLog::query()
        ->where('event_registration_id', $registration->getKey())
        ->where('type', EventNotificationTypeEnum::Reminder)
        ->orderBy('scheduled_for')
        ->pluck('notification_key')
        ->all())->toBe([
            'reminder:10080',
            'reminder:1440',
            'reminder:60',
        ]);

    $optedOutOccurrence = EventOccurrence::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-06-10 10:00:00', 'UTC'),
    ]);
    $optedOutOccurrence->event->forceFill([
        'notification_settings' => [
            'reminders_enabled' => false,
            'reminder_offsets_minutes' => [60],
        ],
    ])->save();
    $optedOutRegistration = EventRegistration::factory()->for($optedOutOccurrence, 'occurrence')->create([
        'email' => 'quiet-attendee@example.com',
    ]);

    ScheduleEventNotificationsAction::run($optedOutRegistration);

    expect(EventNotificationLog::query()
        ->where('event_registration_id', $optedOutRegistration->getKey())
        ->where('type', EventNotificationTypeEnum::Reminder)
        ->count())->toBe(0);
});

it('processes due queued reminder notifications without sending future reminders', function (): void {
    Notification::fake();

    $dueOccurrence = EventOccurrence::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-06-10 10:00:00', 'UTC'),
    ]);
    $futureOccurrence = EventOccurrence::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-06-11 10:00:00', 'UTC'),
    ]);
    $dueRegistration = EventRegistration::factory()->for($dueOccurrence, 'occurrence')->create([
        'email' => 'due-attendee@example.test',
    ]);
    $futureRegistration = EventRegistration::factory()->for($futureOccurrence, 'occurrence')->create([
        'email' => 'future-attendee@example.test',
    ]);

    $dueLog = EventNotificationLog::query()->create([
        'event_occurrence_id' => $dueOccurrence->getKey(),
        'event_registration_id' => $dueRegistration->getKey(),
        'type' => EventNotificationTypeEnum::Reminder,
        'recipient_email' => $dueRegistration->email,
        'status' => 'queued',
        'scheduled_for' => now()->subMinute(),
    ]);
    $futureLog = EventNotificationLog::query()->create([
        'event_occurrence_id' => $futureOccurrence->getKey(),
        'event_registration_id' => $futureRegistration->getKey(),
        'type' => EventNotificationTypeEnum::Reminder,
        'recipient_email' => $futureRegistration->email,
        'status' => 'queued',
        'scheduled_for' => now()->addHour(),
    ]);

    expect(ProcessDueEventNotificationLogsAction::run())->toBe(1)
        ->and($dueLog->refresh()->status)->toBe('sent')
        ->and($dueLog->sent_at)->not->toBeNull()
        ->and($futureLog->refresh()->status)->toBe('queued');

    Notification::assertSentOnDemand(EventRegistrationNotification::class);
});

it('registers scheduled processing for due event notification logs', function (): void {
    $schedule = new Schedule;
    app()->instance(Schedule::class, $schedule);

    $provider = new EventsServiceProvider(app());
    $method = new ReflectionMethod(EventsServiceProvider::class, 'registerSchedule');
    $method->invoke($provider);

    $event = collect($schedule->events())
        ->first(fn (mixed $scheduledEvent): bool => $scheduledEvent->description === 'capell-events:process-notifications');

    throw_unless($event instanceof ScheduledEvent, RuntimeException::class, 'Expected events notification schedule to be registered.');

    expect($event->withoutOverlapping)->toBeTrue()
        ->and($event->onOneServer)->toBeTrue();

    $waitlistEvent = collect($schedule->events())
        ->first(fn (mixed $scheduledEvent): bool => $scheduledEvent->description === 'capell-events:reconcile-waitlists');

    throw_unless($waitlistEvent instanceof ScheduledEvent, RuntimeException::class, 'Expected events waitlist reconcile schedule to be registered.');

    expect($waitlistEvent->withoutOverlapping)->toBeTrue()
        ->and($waitlistEvent->onOneServer)->toBeTrue();
});

it('hydrates public event schema occurrences before building JSON-LD', function (): void {
    $language = Language::factory()->english()->create();
    $occurrence = EventOccurrence::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-06-10 10:00:00', 'UTC'),
        'booking_url' => 'https://example.test/book',
        'capacity' => null,
    ]);
    $event = $occurrence->event;
    $site = $event->site;

    SiteDomain::factory()->for($site)->for($language)->default()->create();
    PageUrl::factory()
        ->page($event)
        ->site($site)
        ->language($language)
        ->state(['url' => '/events/lazy-safe'])
        ->create();
    $event->translations()->create([
        'language_id' => $language->getKey(),
        'title' => 'Hydrated schema event',
        'meta' => ['meta_description' => 'Schema description'],
    ]);

    $resolvedOccurrence = ResolvePublicEventSchemaOccurrenceAction::run($event);

    throw_unless($resolvedOccurrence instanceof EventOccurrence, RuntimeException::class, 'Expected public event occurrence.');

    Model::preventLazyLoading();

    try {
        $schema = BuildEventSchemaAction::run($resolvedOccurrence);
    } finally {
        Model::preventLazyLoading(false);
    }

    expect($schema['name'])->toBe('Hydrated schema event')
        ->and($schema['url'])->toEndWith('/events/lazy-safe/2026-06-10');
});

it('falls back safely when public calendar month state is tampered', function (): void {
    $event = Event::factory()->create();
    bindEventsFrontendSite($event->site);

    $calendar = new EventCalendar;
    $calendar->mount('../../not-a-month');
    $calendar->nextMonth();
    $calendar->previousMonth();

    expect($calendar->month)->toMatch('/^\d{4}-\d{2}$/');
});

it('refreshes occurrence registration count after waitlist promotion', function (): void {
    Notification::fake();

    $occurrence = EventOccurrence::factory()->create([
        'capacity' => 1,
        'registration_count' => 1,
    ]);

    $confirmedRegistration = EventRegistration::factory()->for($occurrence, 'occurrence')->create([
        'status' => EventRegistrationStatusEnum::Confirmed,
    ]);

    EventRegistration::factory()->for($occurrence, 'occurrence')->create([
        'status' => EventRegistrationStatusEnum::Waitlisted,
        'waitlist_position' => 1,
    ]);

    UpdateRegistrationStatusAction::run($confirmedRegistration, EventRegistrationStatusEnum::Cancelled);

    expect($occurrence->refresh()->registration_count)->toBe(1)
        ->and($occurrence->registrations()->where('status', EventRegistrationStatusEnum::Pending)->count())->toBe(1);
});

it('registers the cancellation listener and scheduled waitlist reconcile action', function (): void {
    expect(EventFacade::hasListeners(EventRegistrationCancelled::class))->toBeTrue()
        ->and(class_exists(ReconcileEventWaitlistsAction::class))->toBeTrue();
});

function bindEventsFrontendSite(Site $site): void
{
    app()->instance(CapellFrontendContext::class, new CapellFrontendContext(new readonly class($site) implements FrontendContextReader
    {
        public function __construct(private Site $site) {}

        public function site(): Site
        {
            return $this->site;
        }

        public function language(): ?Language
        {
            return null;
        }

        public function page(): ?Pageable
        {
            return null;
        }

        public function layout(): ?Layout
        {
            return null;
        }

        public function theme(): ?Theme
        {
            return null;
        }

        public function params(): array
        {
            return [];
        }

        public function slug(): ?string
        {
            return null;
        }

        public function isError(): bool
        {
            return false;
        }

        public function setFrontendData(string $key, mixed $value): self
        {
            return $this;
        }

        public function getFrontendData(?string $key = null): mixed
        {
            return $key === null ? [] : null;
        }
    }));
}
