<?php

declare(strict_types=1);

use Capell\Core\Models\Site;
use Capell\PublishingStudio\Actions\DashboardReports\BuildVisibleEditorialCalendarEventsAction;
use Capell\PublishingStudio\Contracts\EditorialCalendarEventContributor;
use Capell\PublishingStudio\Data\EditorialCalendarEventData;
use Capell\PublishingStudio\Data\EditorialCalendarQueryData;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('builds visible editorial calendar events from package contributors', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    app()->bind(
        'publishing-studio.tests.visible-editorial-calendar-contributor',
        fn (): EditorialCalendarEventContributor => new class implements EditorialCalendarEventContributor
        {
            public function editorialCalendarEvents(EditorialCalendarQueryData $query): iterable
            {
                return [
                    new EditorialCalendarEventData(
                        id: 'newsletter-send-10',
                        sourcePackage: 'capell-app/newsletter',
                        sourceType: 'newsletter',
                        sourceId: '10',
                        title: 'Newsletter launch',
                        eventType: 'newsletter.send',
                        startsAt: CarbonImmutable::parse('2026-05-03 12:00:00', 'UTC'),
                        eventTypeLabel: 'Newsletter send',
                        state: 'scheduled',
                        siteId: 7,
                    ),
                ];
            }
        },
    );
    app()->tag(['publishing-studio.tests.visible-editorial-calendar-contributor'], EditorialCalendarEventContributor::TAG);

    $events = BuildVisibleEditorialCalendarEventsAction::run(
        startsAt: CarbonImmutable::parse('2026-05-01 00:00:00', 'UTC'),
        endsAt: CarbonImmutable::parse('2026-05-31 23:59:59', 'UTC'),
    );

    expect($events)->toHaveCount(1)
        ->and($events->first()?->sourcePackage)->toBe('capell-app/newsletter')
        ->and($events->first()?->sourceType)->toBe('newsletter')
        ->and($events->first()?->title)->toBe('Newsletter launch');
});

it('limits visible editorial calendar events to assigned sites for scoped editors', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    $site = Site::factory()->create();
    $otherSite = Site::factory()->create();
    Auth::setUser(new class((int) $site->getKey()) implements Authenticatable
    {
        public function __construct(private readonly int $siteId) {}

        public function getAuthIdentifierName(): string
        {
            return 'id';
        }

        public function getAuthIdentifier(): int
        {
            return 123;
        }

        public function getAuthPasswordName(): string
        {
            return 'password';
        }

        public function getAuthPassword(): string
        {
            return '';
        }

        public function getRememberToken(): ?string
        {
            return null;
        }

        public function setRememberToken(mixed $value): void {}

        public function getRememberTokenName(): string
        {
            return 'remember_token';
        }

        public function isGlobalAdmin(): bool
        {
            return false;
        }

        /**
         * @return Collection<int, int>
         */
        public function getAssignedSiteIds(): Collection
        {
            return collect([$this->siteId]);
        }
    });

    app()->bind(
        'publishing-studio.tests.scoped-visible-editorial-calendar-contributor',
        fn (): EditorialCalendarEventContributor => new class($site, $otherSite) implements EditorialCalendarEventContributor
        {
            public function __construct(
                private readonly Site $site,
                private readonly Site $otherSite,
            ) {}

            public function editorialCalendarEvents(EditorialCalendarQueryData $query): iterable
            {
                return [
                    new EditorialCalendarEventData(
                        id: 'event-1',
                        sourcePackage: 'capell-app/events',
                        sourceType: 'event',
                        sourceId: '1',
                        title: 'Assigned site event',
                        eventType: 'event.occurrence',
                        startsAt: CarbonImmutable::parse('2026-05-03 12:00:00', 'UTC'),
                        siteId: (int) $this->site->getKey(),
                    ),
                    new EditorialCalendarEventData(
                        id: 'event-2',
                        sourcePackage: 'capell-app/events',
                        sourceType: 'event',
                        sourceId: '2',
                        title: 'Other site event',
                        eventType: 'event.occurrence',
                        startsAt: CarbonImmutable::parse('2026-05-04 12:00:00', 'UTC'),
                        siteId: (int) $this->otherSite->getKey(),
                    ),
                ];
            }
        },
    );
    app()->tag(['publishing-studio.tests.scoped-visible-editorial-calendar-contributor'], EditorialCalendarEventContributor::TAG);

    $events = BuildVisibleEditorialCalendarEventsAction::run(
        startsAt: CarbonImmutable::parse('2026-05-01 00:00:00', 'UTC'),
        endsAt: CarbonImmutable::parse('2026-05-31 23:59:59', 'UTC'),
    );

    expect($events)->toHaveCount(1)
        ->and($events->first()?->title)->toBe('Assigned site event');
});
