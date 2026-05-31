<?php

declare(strict_types=1);

use Capell\Admin\Data\MessageData;
use Capell\Admin\Enums\AlertTypeEnum;
use Capell\Core\Enums\PublishStatusEnum;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\HtmlCache\Models\CachedModelUrl;
use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Capell\PublishingStudio\Enums\WorkspaceApprovalActionEnum;
use Capell\PublishingStudio\Enums\WorkspaceStatusEnum;
use Capell\PublishingStudio\Filament\Pages\Tables\ScheduledPublishingTable;
use Capell\PublishingStudio\Filament\Widgets\PageAlertsWidget;
use Capell\PublishingStudio\Livewire\PageApprovalStatus;
use Capell\PublishingStudio\Livewire\WorkspaceApprovalHistory;
use Capell\PublishingStudio\Models\SchedulerEvent;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Models\WorkspaceApproval;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

uses(CreatesAdminUser::class);

it('configures the scheduled publishing table columns filters actions and pagination', function (): void {
    $table = ScheduledPublishingTable::configure(publishingStudioTableForCoverage());

    expect(array_keys($table->getColumns()))->toBe([
        'title',
        'event_type_label',
        'source_type',
        'status',
        'scheduled_for',
        'description',
        'failure',
        'timezone',
    ])
        ->and(array_keys($table->getFilters()))->toBe([
            'event_type',
            'source_type',
            'state',
            'quick',
        ])
        ->and(collect($table->getActions())
            ->filter(fn (mixed $action): bool => method_exists($action, 'getName'))
            ->map(fn (object $action): string => $action->getName())
            ->all())->toBe([
                'details',
                'retry',
                'cancel',
            ])
        ->and($table->getDefaultSortColumn())->toBe('scheduled_for')
        ->and($table->getDefaultSortDirection())->toBe('asc')
        ->and($table->getPaginationPageOptions())->toBe([10, 25, 50]);
});

it('filters and sorts scheduled publishing table records from scheduler events', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-20 09:00:00', 'UTC'));

    $site = Site::factory()->create(['name' => 'Main Site']);
    $workspace = Workspace::factory()->create(['name' => 'Release Workspace']);
    $failedWorkspace = Workspace::factory()->create(['name' => 'Failed Release']);

    $scheduled = schedulerEventForCoverage(
        workspace: $workspace,
        siteId: (int) $site->getKey(),
        eventType: SchedulerEventTypeEnum::Publish,
        state: SchedulerEventStateEnum::Scheduled,
        scheduledFor: CarbonImmutable::parse('2026-05-21 10:00:00', 'UTC'),
    );
    $failed = schedulerEventForCoverage(
        workspace: $failedWorkspace,
        siteId: (int) $site->getKey(),
        eventType: SchedulerEventTypeEnum::Unpublish,
        state: SchedulerEventStateEnum::Failed,
        scheduledFor: CarbonImmutable::parse('2026-05-22 10:00:00', 'UTC'),
        failure: 'Scheduler failed',
    );
    $blocked = schedulerEventForCoverage(
        workspace: $workspace,
        siteId: (int) $site->getKey(),
        eventType: SchedulerEventTypeEnum::ReviewReminder,
        state: SchedulerEventStateEnum::SkippedEmbargo,
        scheduledFor: CarbonImmutable::parse('2026-05-23 10:00:00', 'UTC'),
    );

    $records = new ReflectionMethod(ScheduledPublishingTable::class, 'records');

    $allRecords = $records->invoke(null, ['source_type' => ['value' => 'workspace']], 'release', 'title', 'desc');
    $failedRecords = $records->invoke(null, [
        'source_type' => 'workspace',
        'quick' => 'failed',
    ], null, 'scheduled_for', 'asc');
    $blockedRecords = $records->invoke(null, [
        'source_type' => 'workspace',
        'quick' => 'blocked',
    ], null, 'scheduled_for', 'asc');
    $unpublishRecords = $records->invoke(null, [
        'source_type' => 'workspace',
        'quick' => 'automatic_unpublishes',
    ], null, 'status', 'asc');
    $reviewRecords = $records->invoke(null, [
        'source_type' => 'workspace',
        'quick' => 'review_reminders_due',
    ], null, 'scheduled_for', 'asc');

    expect($allRecords->pluck('id')->all())->toContain('scheduler-event-' . $scheduled->id, 'scheduler-event-' . $failed->id)
        ->and($failedRecords)->toHaveCount(1)
        ->and($failedRecords->first()['id'])->toBe('scheduler-event-' . $failed->id)
        ->and($blockedRecords)->toHaveCount(1)
        ->and($blockedRecords->first()['id'])->toBe('scheduler-event-' . $blocked->id)
        ->and($unpublishRecords)->toHaveCount(1)
        ->and($unpublishRecords->first()['event_type'])->toBe(SchedulerEventTypeEnum::Unpublish->value)
        ->and($reviewRecords)->toHaveCount(1)
        ->and($reviewRecords->first()['event_type'])->toBe(SchedulerEventTypeEnum::ReviewReminder->value);
});

it('builds scheduled publishing table helper values and safe details markup', function (): void {
    $workspace = Workspace::factory()->create(['name' => 'Details Workspace']);
    $event = schedulerEventForCoverage(
        workspace: $workspace,
        siteId: null,
        eventType: SchedulerEventTypeEnum::Publish,
        state: SchedulerEventStateEnum::Scheduled,
        scheduledFor: CarbonImmutable::parse('2026-05-21 12:00:00', 'UTC'),
    );

    $sortValue = new ReflectionMethod(ScheduledPublishingTable::class, 'sortValue');

    $filterValue = new ReflectionMethod(ScheduledPublishingTable::class, 'filterValue');

    $eventFromRecord = new ReflectionMethod(ScheduledPublishingTable::class, 'eventFromRecord');

    $canUseSite = new ReflectionMethod(ScheduledPublishingTable::class, 'canUseSite');

    $details = new ReflectionMethod(ScheduledPublishingTable::class, 'details');

    $record = [
        'id' => 'scheduler-event-' . $event->id,
        'title' => 'Details Workspace',
        'event_type_label' => '<Publish>',
        'state_label' => 'Scheduled',
        'status' => 'Scheduled',
        'scheduled_for' => CarbonImmutable::parse('2026-05-21 12:00:00', 'UTC'),
        'timezone' => 'UTC',
        'failure' => '<broken>',
    ];

    $markup = $details->invoke(null, $record);

    expect($sortValue->invoke(null, ['title' => 'Zebra'], 'title'))->toBe('zebra')
        ->and($sortValue->invoke(null, ['scheduled_for' => '2026-05-21'], 'unknown'))->toBe('2026-05-21')
        ->and($filterValue->invoke(null, ['state' => ['value' => SchedulerEventStateEnum::Scheduled->value]], 'state'))->toBe(SchedulerEventStateEnum::Scheduled->value)
        ->and($filterValue->invoke(null, ['state' => ''], 'state'))->toBeNull()
        ->and($eventFromRecord->invoke(null, ['id' => 'page-1-publish']))->toBeNull()
        ->and($eventFromRecord->invoke(null, $record))->toBeInstanceOf(SchedulerEvent::class)
        ->and($canUseSite->invoke(null, null))->toBeTrue()
        ->and($markup)->toBeInstanceOf(HtmlString::class)
        ->and($markup->toHtml())->toContain('&lt;Publish&gt;')
        ->and($markup->toHtml())->toContain('&lt;broken&gt;');
});

it('runs scheduled publishing retry and cancel table actions against scheduler event records', function (): void {
    $this->actingAsAdmin();

    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-21 09:00:00', 'UTC'));

    $workspace = Workspace::factory()->create(['name' => 'Table Action Workspace']);
    $failedEvent = schedulerEventForCoverage(
        workspace: $workspace,
        siteId: null,
        eventType: SchedulerEventTypeEnum::Embargo,
        state: SchedulerEventStateEnum::Failed,
        scheduledFor: CarbonImmutable::parse('2026-05-21 08:00:00', 'UTC'),
        failure: 'Previous failure',
    );
    $scheduledEvent = schedulerEventForCoverage(
        workspace: $workspace,
        siteId: null,
        eventType: SchedulerEventTypeEnum::ReviewReminder,
        state: SchedulerEventStateEnum::Scheduled,
        scheduledFor: CarbonImmutable::parse('2026-05-22 08:00:00', 'UTC'),
    );

    $table = ScheduledPublishingTable::configure(publishingStudioTableForCoverage());
    $retry = publishingStudioTableActionForCoverage($table, 'retry');
    $cancel = publishingStudioTableActionForCoverage($table, 'cancel');
    $failedRecord = $failedEvent->fresh();
    $scheduledRecord = $scheduledEvent->fresh();

    expect($failedRecord)->toBeInstanceOf(SchedulerEvent::class)
        ->and($scheduledRecord)->toBeInstanceOf(SchedulerEvent::class);

    $failedRecordData = scheduledPublishingTableRecordForCoverage(publishingStudioTestInstance($failedRecord, SchedulerEvent::class));
    $scheduledRecordData = scheduledPublishingTableRecordForCoverage(publishingStudioTestInstance($scheduledRecord, SchedulerEvent::class));

    publishingStudioRunTableAction($retry, $failedRecordData);
    publishingStudioRunTableAction($cancel, $scheduledRecordData);

    expect($failedEvent->refresh()->state)->toBe(SchedulerEventStateEnum::Executed)
        ->and($failedEvent->claim_token)->toBeNull()
        ->and($scheduledEvent->refresh()->state)->toBe(SchedulerEventStateEnum::Cancelled)
        ->and($scheduledEvent->skipped_reason)->toBe('cancelled_by_editor');
});

it('renders page approval status for review and rejected workspaces', function (): void {
    $this->actingAsAdmin();

    $reviewWorkspace = Workspace::factory()->inReview()->create();
    $openWorkspace = Workspace::factory()->open()->create();
    $reviewPage = Page::factory()->create(['workspace_id' => $reviewWorkspace->id]);
    $openPage = Page::factory()->create(['workspace_id' => $openWorkspace->id]);
    $reviewer = $this->createUser();

    WorkspaceApproval::factory()
        ->workspace($reviewWorkspace)
        ->actionable($reviewer)
        ->approved()
        ->create();
    WorkspaceApproval::factory()
        ->workspace($openWorkspace)
        ->actionable($reviewer)
        ->create(['action' => WorkspaceApprovalActionEnum::Rejected]);

    $widget = new PageApprovalStatus;
    $widget->record = $reviewPage;

    $reviewView = $widget->render();

    $visibleFor = new ReflectionMethod(PageApprovalStatus::class, 'isVisibleFor');

    $titleFor = new ReflectionMethod(PageApprovalStatus::class, 'titleFor');

    $approvalsFor = new ReflectionMethod(PageApprovalStatus::class, 'approvalsFor');

    expect($reviewView->getData()['visible'])->toBeTrue()
        ->and($reviewView->getData()['title'])->toBeString()
        ->and($reviewView->getData()['approvals'])->toHaveCount(1)
        ->and($visibleFor->invoke($widget, null, null))->toBeFalse()
        ->and($visibleFor->invoke($widget, $openWorkspace, WorkspaceApprovalActionEnum::Rejected))->toBeTrue()
        ->and($titleFor->invoke($widget, WorkspaceStatusEnum::Approved, null))->toBeString()
        ->and($titleFor->invoke($widget, WorkspaceStatusEnum::Open, WorkspaceApprovalActionEnum::ChangesRequested))->toBeString()
        ->and($titleFor->invoke($widget, WorkspaceStatusEnum::Open, null))->toBe('')
        ->and($approvalsFor->invoke($widget, null))->toHaveCount(0);

    $openWidget = new PageApprovalStatus;
    $openWidget->record = $openPage;

    $openView = $openWidget->render();

    expect($openView->getData()['visible'])->toBeTrue()
        ->and($openView->getData()['approvals']->first()->action)->toBe(WorkspaceApprovalActionEnum::Rejected);
});

it('loads workspace approval history for mounted records only', function (): void {
    $this->actingAsAdmin();

    $workspace = Workspace::factory()->create();
    $user = $this->createUser();
    WorkspaceApproval::factory()
        ->workspace($workspace)
        ->actionable($user)
        ->create(['action' => WorkspaceApprovalActionEnum::ChangesRequested]);

    $component = new WorkspaceApprovalHistory;
    $component->mount();

    $loadApprovals = new ReflectionMethod(WorkspaceApprovalHistory::class, 'loadApprovals');

    $emptyApprovals = $loadApprovals->invoke($component);

    $component->mount($workspace);
    $approvals = $loadApprovals->invoke($component);

    expect($emptyApprovals)->toHaveCount(0)
        ->and($component->workspaceId)->toBe($workspace->id)
        ->and($approvals)->toHaveCount(1)
        ->and($approvals->first()->action)->toBe(WorkspaceApprovalActionEnum::ChangesRequested);
});

it('builds page alerts for draft cache status deleted site and canonical references', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-20 09:00:00', 'UTC'));

    $site = Site::factory()->withTranslations()->create();
    $workspace = Workspace::factory()->create(['name' => 'Launch Workspace']);
    $page = Page::factory()->site($site)->create([
        'workspace_id' => $workspace->id,
        'visible_from' => CarbonImmutable::parse('2026-05-21 09:00:00', 'UTC'),
    ]);

    PageUrl::withoutEvents(
        fn (): PageUrl => PageUrl::factory()
            ->page($page)
            ->site($site)
            ->language($site->language)
            ->state(['url' => '/draft'])
            ->create(),
    );

    Page::factory()
        ->site($site)
        ->canonicalPage($page)
        ->create();

    CachedModelUrl::query()->create([
        'url' => 'https://example.test/draft',
        'url_hash' => CachedModelUrl::hashUrl('https://example.test/draft'),
        'path' => '/draft',
        'site_id' => $site->id,
        'language_id' => $site->language_id,
        'cacheable_type' => $page->getMorphClass(),
        'cacheable_id' => $page->getKey(),
        'cached_at' => CarbonImmutable::parse('2026-05-20 08:30:00', 'UTC'),
        'last_seen_at' => CarbonImmutable::parse('2026-05-20 08:45:00', 'UTC'),
    ]);

    $site->delete();

    $widget = pageAlertsWidgetForCoverage($page->fresh());
    $widget->mount();

    $alerts = $widget->alerts();

    $pageStatusAlert = publishingStudioTestInstance($alerts->get('pageStatus'), MessageData::class);
    $deletedSiteAlert = publishingStudioTestInstance($alerts->get('deleted_site'), MessageData::class);
    $referencedAlert = publishingStudioTestInstance($alerts->get('referenced'), MessageData::class);
    $pendingAlert = publishingStudioTestInstance($alerts->get('pending'), MessageData::class);
    $cachedAlert = publishingStudioTestInstance($alerts->get('cached'), MessageData::class);

    expect($alerts->keys()->all())->toContain('pageStatus', 'deleted_site', 'referenced', 'pending', 'cached')
        ->and($pageStatusAlert->message)->toContain('Launch Workspace')
        ->and($pageStatusAlert->type)->toBe(AlertTypeEnum::Info)
        ->and($pageStatusAlert->action)->toHaveCount(2)
        ->and($deletedSiteAlert->type)->toBe(AlertTypeEnum::Warning)
        ->and($referencedAlert->type)->toBe(AlertTypeEnum::Info)
        ->and($pendingAlert->type)->toBe(AlertTypeEnum::Warning)
        ->and($cachedAlert->type)->toBe(AlertTypeEnum::Info);
});

it('guards page alerts records and covers missing url deleted and expired branches', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-20 09:00:00', 'UTC'));

    $widgetWithoutRecord = pageAlertsWidgetForCoverage(null);

    expect($widgetWithoutRecord->alerts())->toBeEmpty();

    $expiredPage = Page::factory()->create([
        'visible_until' => CarbonImmutable::parse('2026-05-19 09:00:00', 'UTC'),
    ]);

    $expiredWidget = pageAlertsWidgetForCoverage($expiredPage->fresh());
    $expiredWidget->mount();

    $expiredAlerts = $expiredWidget->alerts();

    $deletedPage = Page::factory()->create();
    $deletedPage->delete();

    $deletedWidget = pageAlertsWidgetForCoverage($deletedPage->fresh());
    $deletedWidget->mount();

    $deletedAlerts = $deletedWidget->alerts();

    $missingUrlAlert = publishingStudioTestInstance($expiredAlerts->get('missingUrl'), MessageData::class);
    $expiredAlert = publishingStudioTestInstance($expiredAlerts->get('expired'), MessageData::class);
    $deletedAlert = publishingStudioTestInstance($deletedAlerts->get('deleted'), MessageData::class);

    expect($expiredPage->fresh()->publish_status)->toBe(PublishStatusEnum::expired)
        ->and($expiredAlerts->keys()->all())->toContain('missingUrl', 'expired')
        ->and($expiredAlerts->keys()->all())->not->toContain('pageStatus')
        ->and($missingUrlAlert->type)->toBe(AlertTypeEnum::Warning)
        ->and($expiredAlert->type)->toBe(AlertTypeEnum::Warning)
        ->and($deletedAlerts->keys()->all())->toContain('deleted', 'missingUrl')
        ->and($deletedAlert->type)->toBe(AlertTypeEnum::Warning);
});

it('hydrates page alerts from route and record keys while running widget actions', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-20 09:00:00', 'UTC'));

    $site = Site::factory()->withTranslations()->create();
    $page = Page::factory()->site($site)->withTranslations()->create();

    PageUrl::withoutEvents(
        fn (): PageUrl => PageUrl::factory()
            ->page($page)
            ->site($site)
            ->language($site->language)
            ->state(['url' => '/routed-page'])
            ->create(),
    );

    Page::factory()
        ->site($site)
        ->canonicalPage($page)
        ->create();

    CachedModelUrl::query()->create([
        'url' => 'https://example.test/routed-page',
        'url_hash' => CachedModelUrl::hashUrl('https://example.test/routed-page'),
        'path' => '/routed-page',
        'site_id' => $site->id,
        'language_id' => $site->language_id,
        'cacheable_type' => $page->getMorphClass(),
        'cacheable_id' => $page->getKey(),
        'cached_at' => CarbonImmutable::parse('2026-05-20 08:30:00', 'UTC'),
        'last_seen_at' => CarbonImmutable::parse('2026-05-20 08:45:00', 'UTC'),
    ]);

    request()->setRouteResolver(static fn (): object => publishingStudioRouteRecord($page));

    $routeWidget = pageAlertsWidgetForCoverage(null);
    $routeWidget->mount();

    $keyWidget = pageAlertsWidgetForCoverage(null);
    $keyWidget->recordKey = (int) $page->getKey();
    $keyWidget->hydrate();

    $keyAlerts = $keyWidget->alerts();

    publishingStudioRunWidgetAction($keyWidget->clearCacheAction());
    publishingStudioRunWidgetAction(pageAlertsWidgetForCoverage(null)->clearCacheAction());

    request()->setRouteResolver(static fn (): object => publishingStudioRouteRecord((string) $page->getKey()));

    $numericRouteWidget = pageAlertsWidgetForCoverage(null);
    $numericRouteWidget->mount();

    expect($routeWidget->recordKey)->toBe($page->getKey())
        ->and($numericRouteWidget->recordKey)->toBe($page->getKey())
        ->and($keyAlerts->keys()->all())->toContain('cached', 'referenced')
        ->and($keyWidget->viewSiteAction()->getUrl())->toContain((string) $site->getKey())
        ->and($keyWidget->viewCanonicalsAction()->isVisible())->toBeTrue()
        ->and($keyWidget->viewCanonicalsAction()->getUrl())->toContain((string) $page->getKey());
});

function schedulerEventForCoverage(
    Workspace $workspace,
    ?int $siteId,
    SchedulerEventTypeEnum $eventType,
    SchedulerEventStateEnum $state,
    CarbonImmutable $scheduledFor,
    ?string $failure = null,
): SchedulerEvent {
    return SchedulerEvent::query()->create([
        'uuid' => (string) Str::uuid(),
        'event_type' => $eventType,
        'state' => $state,
        'source_type' => 'workspace',
        'source_id' => $workspace->id,
        'workspace_id' => $workspace->id,
        'site_id' => $siteId,
        'scheduled_for' => $scheduledFor,
        'display_timezone' => 'UTC',
        'idempotency_key' => (string) Str::uuid(),
        'last_failure_message' => $failure,
    ]);
}

function publishingStudioTableForCoverage(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null);

    return Table::make($livewire);
}

function publishingStudioTableActionForCoverage(Table $table, string $name): Action
{
    $action = collect($table->getActions())
        ->first(fn (mixed $candidate): bool => $candidate instanceof Action && $candidate->getName() === $name);

    expect($action)->toBeInstanceOf(Action::class);

    return publishingStudioTestInstance($action, Action::class);
}

/**
 * @param  array<string, mixed>  $record
 */
function publishingStudioRunTableAction(Action $action, array $record): void
{
    $closure = $action->getActionFunction();

    expect($closure)->not->toBeNull();

    throw_if(! $closure instanceof Closure, RuntimeException::class, 'Scheduled publishing action must define a callable action.');

    $action->evaluate($closure, ['record' => $record]);
}

function publishingStudioRunWidgetAction(Action $action): void
{
    $closure = $action->getActionFunction();

    expect($closure)->not->toBeNull();

    throw_if(! $closure instanceof Closure, RuntimeException::class, 'Publishing Studio widget action must define a callable action.');

    $action->evaluate($closure);
}

function publishingStudioRouteRecord(mixed $record): object
{
    return new readonly class($record)
    {
        public function __construct(private mixed $record) {}

        public function parameter(string $key, mixed $default = null): mixed
        {
            return $key === 'record' ? $this->record : $default;
        }
    };
}

/**
 * @return array<string, mixed>
 */
function scheduledPublishingTableRecordForCoverage(SchedulerEvent $event): array
{
    return [
        'id' => 'scheduler-event-' . $event->getKey(),
        'source_type' => $event->source_type,
        'source_id' => $event->source_id,
        'title' => $event->workspace?->name ?? 'Scheduler event',
        'event_type' => $event->event_type->value,
        'event_type_label' => $event->event_type->getLabel(),
        'event_type_color' => $event->event_type->getColor(),
        'scheduled_for' => $event->scheduled_for,
        'status' => $event->state->getLabel(),
        'description' => null,
        'record_url' => null,
        'state' => $event->state->value,
        'state_label' => $event->state->getLabel(),
        'state_color' => $event->state->getColor(),
        'site_id' => $event->site_id,
        'site_name' => null,
        'owner_id' => null,
        'owner_name' => null,
        'timezone' => $event->display_timezone,
        'failure' => $event->last_failure_message,
    ];
}

function pageAlertsWidgetForCoverage(?Page $page): PageAlertsWidget
{
    return new class($page) extends PageAlertsWidget
    {
        public function __construct(?Page $page)
        {
            $this->record = $page;
        }
    };
}
