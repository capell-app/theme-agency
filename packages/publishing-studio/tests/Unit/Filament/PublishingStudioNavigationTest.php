<?php

declare(strict_types=1);

use Capell\PublishingStudio\Data\PublishReadinessData;
use Capell\PublishingStudio\Enums\WorkspaceStatusEnum;
use Capell\PublishingStudio\Filament\Pages\ScheduledPublishingPage;
use Capell\PublishingStudio\Filament\Pages\StaleDraftsPage;
use Capell\PublishingStudio\Filament\Resources\PreviewLinks\PreviewLinkResource;
use Capell\PublishingStudio\Filament\Resources\PublishingStudio\Actions\PublishAction;
use Capell\PublishingStudio\Filament\Resources\PublishingStudio\Actions\RollbackAction;
use Capell\PublishingStudio\Filament\Resources\PublishingStudio\Actions\ValidateAction;
use Capell\PublishingStudio\Filament\Resources\PublishingStudio\WorkspaceResource;
use Capell\PublishingStudio\Models\Version;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\RebaseReport;
use Capell\Tests\Fixtures\Models\User;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

it('moves publishing workflow utilities out of content navigation', function (): void {
    expect(WorkspaceResource::getNavigationGroup())->toBe((string) __('capell-admin::navigation.group_workflow'))
        ->and(ScheduledPublishingPage::getNavigationGroup())->toBe((string) __('capell-admin::navigation.group_workflow'))
        ->and(ScheduledPublishingPage::getNavigationItems()[0]->getSort())->toBe(1)
        ->and(StaleDraftsPage::getNavigationGroup())->toBe((string) __('capell-admin::navigation.group_workflow'))
        ->and(StaleDraftsPage::getNavigationItems()[0]->getSort())->toBe(2)
        ->and(PreviewLinkResource::getNavigationGroup())->toBe((string) __('capell-admin::navigation.group_workflow'))
        ->and(PreviewLinkResource::getNavigationItems()[0]->getSort())->toBe(3);
});

it('builds publish and rollback action surfaces from workspace state', function (): void {
    Gate::before(static fn (User $user): bool => true);
    test()->actingAs(User::factory()->create());

    $approvedWorkspace = Workspace::factory()->create([
        'status' => WorkspaceStatusEnum::Approved,
    ]);
    $draftWorkspace = Workspace::factory()->create([
        'status' => WorkspaceStatusEnum::Open,
    ]);

    $previousVersion = Version::query()->create([
        'uuid' => (string) Str::uuid(),
        'number' => Version::query()->max('number') + 1,
        'name' => 'Previous live version',
        'is_live' => false,
        'manifest' => [],
        'published_at' => now()->subDay(),
    ]);
    $publishedVersion = Version::query()->create([
        'uuid' => (string) Str::uuid(),
        'number' => $previousVersion->number + 1,
        'name' => 'Published workspace version',
        'is_live' => false,
        'manifest' => [],
        'source_workspace_id' => $approvedWorkspace->getKey(),
        'published_at' => now(),
    ]);

    $publishAction = PublishAction::make('publish')
        ->record($approvedWorkspace);
    $rollbackAction = RollbackAction::make('rollback')
        ->record($approvedWorkspace);

    expect(PublishAction::getDefaultName())->toBe('publish')
        ->and(RollbackAction::getDefaultName())->toBe('rollback')
        ->and($publishAction->isVisible())->toBeTrue()
        ->and(PublishAction::make('publish')->record($draftWorkspace)->isVisible())->toBeFalse()
        ->and($publishAction->getModalDescription())->toBeString()
        ->and($publishAction->getSchema(Schema::make()))->toBeNull()
        ->and($rollbackAction->isVisible())->toBeTrue()
        ->and($rollbackAction->getModalHeading())->toBeString()
        ->and($rollbackAction->getModalDescription())->toBeString()
        ->and($rollbackAction->getSchema(Schema::make())?->getComponents()[0] ?? null)->toBeInstanceOf(Textarea::class)
        ->and(RollbackAction::make('rollback')->record(Workspace::factory()->create())->isVisible())->toBeFalse()
        ->and($publishedVersion->exists)->toBeTrue();
});

it('reports validation action outcomes for failures warnings and clean dry runs', function (): void {
    $workspace = Workspace::factory()->create();
    $notifyFromReadiness = new ReflectionMethod(ValidateAction::class, 'notifyFromReadiness');
    $action = ValidateAction::make('validate');
    $failureReadiness = new PublishReadinessData(
        workspaceId: (int) $workspace->getKey(),
        wouldPublish: false,
        totalRows: 0,
        rowCounts: [],
        collisions: [],
        conflictCount: 0,
        checkResults: [],
        failureMessage: 'Validation failed',
        blockingIssues: ['Validation failed'],
        blockingIssueCount: 1,
    );
    $warningReadiness = new PublishReadinessData(
        workspaceId: (int) $workspace->getKey(),
        wouldPublish: false,
        totalRows: 2,
        rowCounts: [Workspace::class => 2],
        collisions: [['site_id' => 1, 'language_id' => 1, 'url' => '/conflict']],
        conflictCount: 1,
        checkResults: [],
        failureMessage: null,
        blockingIssues: ['URL collision: /conflict.'],
        blockingIssueCount: 1,
    );
    $cleanReadiness = new PublishReadinessData(
        workspaceId: (int) $workspace->getKey(),
        wouldPublish: true,
        totalRows: 3,
        rowCounts: [Workspace::class => 3],
        collisions: [],
        conflictCount: 0,
        checkResults: [],
        failureMessage: null,
        blockingIssues: [],
        blockingIssueCount: 0,
    );

    $notifyFromReadiness->invoke($action, $failureReadiness);
    $notifyFromReadiness->invoke($action, $warningReadiness);
    $notifyFromReadiness->invoke($action, $cleanReadiness);

    expect(ValidateAction::getDefaultName())->toBe('validate')
        ->and($failureReadiness->failureMessage)->toBe('Validation failed')
        ->and($warningReadiness->collisions)->not->toBeEmpty()
        ->and($warningReadiness->conflictCount)->toBe(1)
        ->and($warningReadiness->totalRows)->toBe(2)
        ->and($cleanReadiness->totalRows)->toBe(3);
});

it('handles publish action release window and blocked report branches from workspace state', function (): void {
    Gate::before(static fn (User $user): bool => true);

    test()->actingAs(User::factory()->create());
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-30 12:00:00', 'UTC'));
    config()->set('capell.publishing-studio.release_windows.enabled', true);
    config()->set('capell.publishing-studio.release_windows.timezone', 'UTC');
    config()->set('capell.publishing-studio.release_windows.windows', [
        ['days' => ['mon'], 'start' => '09:00', 'end' => '10:00'],
    ]);

    $workspace = Workspace::factory()->create([
        'status' => WorkspaceStatusEnum::Approved,
    ]);

    $windowSchema = PublishAction::make('publish')
        ->record($workspace)
        ->getSchema(Schema::make());

    $conflictReport = new RebaseReport($workspace, currentLiveVersionId: null, conflicts: []);
    $conflictReport->addConflict(Workspace::class, (string) Str::uuid());

    $blockedAction = PublishAction::make('publish')
        ->record($workspace);

    publishingStudioSeedPublishActionReport(
        $blockedAction,
        $workspace,
        [['site_id' => 1, 'language_id' => 1, 'url' => '/duplicate']],
        $conflictReport,
    );

    publishingStudioInvokeWorkspaceAction($blockedAction, $workspace);

    Auth::logout();

    $noUserAction = PublishAction::make('publish')
        ->record($workspace);

    publishingStudioInvokeWorkspaceAction($noUserAction, $workspace);

    expect($windowSchema?->getComponents()[0] ?? null)->toBeInstanceOf(Checkbox::class)
        ->and($blockedAction->getModalDescription())->toBeString()
        ->and($workspace->fresh()->status)->toBe(WorkspaceStatusEnum::Approved);

    CarbonImmutable::setTestNow();
});

it('reports stale publish failures without mutating the approved workspace', function (): void {
    Gate::before(static fn (User $user): bool => true);

    test()->actingAs(User::factory()->create());
    config()->set('capell.publishing-studio.release_windows.enabled', false);

    Version::query()->update(['is_live' => false]);

    $currentVersion = Version::query()->create([
        'uuid' => (string) Str::uuid(),
        'number' => (int) (Version::query()->max('number') ?? 0) + 1,
        'name' => 'Current live version',
        'is_live' => true,
        'manifest' => [],
        'published_at' => now(),
    ]);

    $workspace = Workspace::factory()->create([
        'status' => WorkspaceStatusEnum::Approved,
        'base_version_id' => max(0, $currentVersion->id - 1),
    ]);

    $action = PublishAction::make('publish')
        ->record($workspace);

    publishingStudioSeedPublishActionReport(
        $action,
        $workspace,
        [],
        new RebaseReport($workspace, currentLiveVersionId: $currentVersion->id, conflicts: []),
    );

    publishingStudioInvokeWorkspaceAction($action, $workspace);

    expect($workspace->fresh()->status)->toBe(WorkspaceStatusEnum::Approved)
        ->and(Version::query()->where('source_workspace_id', $workspace->id)->exists())->toBeFalse();
});

/**
 * @param  array<int|string, mixed>  $collisions
 */
function publishingStudioSeedPublishActionReport(
    PublishAction $action,
    Workspace $workspace,
    array $collisions,
    RebaseReport $report,
): void {
    $property = new ReflectionProperty(PublishAction::class, 'reportCache');
    $property->setValue($action, [
        $workspace->id => [
            'collisions' => $collisions,
            'report' => $report,
        ],
    ]);
}

/**
 * @param  array<string, mixed>  $data
 */
function publishingStudioInvokeWorkspaceAction(Action $action, Workspace $workspace, array $data = []): void
{
    $closure = $action->getActionFunction();

    expect($closure)->not->toBeNull();

    $action->evaluate(
        $closure,
        [
            'record' => $workspace,
            'data' => $data,
        ],
        [
            Action::class => $action,
            Workspace::class => $workspace,
        ],
    );
}
