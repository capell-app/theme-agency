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
use Capell\Events\Actions\QueryPublicEventOccurrencesAction;
use Capell\Events\Actions\RegisterForEventOccurrenceAction;
use Capell\Events\Actions\SendEventNotificationAction;
use Capell\Events\Actions\UpdateRegistrationStatusAction;
use Capell\Events\Data\EventRegistrationData;
use Capell\Events\Enums\EventBookingModeEnum;
use Capell\Events\Enums\EventNotificationTypeEnum;
use Capell\Events\Enums\EventOccurrenceStatusEnum;
use Capell\Events\Enums\EventRegistrationStatusEnum;
use Capell\Events\Enums\EventVisibilityEnum;
use Capell\Events\Filament\Resources\Events\EventResource;
use Capell\Events\Filament\Resources\Events\Pages\CreateEvent;
use Capell\Events\Filament\Resources\Events\Pages\EditEvent;
use Capell\Events\Filament\Resources\Events\Pages\ListEvents;
use Capell\Events\Http\Controllers\CalendarFeedController;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventNotificationLog;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventRegistration;
use Capell\Events\Notifications\EventRegistrationNotification;
use Capell\Events\Providers\EventsServiceProvider;
use Capell\Frontend\Contracts\FrontendContextReader;
use Capell\Frontend\Support\CapellFrontendContext;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
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
    $language = Language::query()->firstOrFail();
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
    $language = Language::query()->firstOrFail();
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

    expect(fn () => RegisterForEventOccurrenceAction::run(
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
