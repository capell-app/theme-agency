<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\PublishingStudio\Enums\WorkspaceKindEnum;
use Capell\PublishingStudio\Enums\WorkspaceStatusEnum;
use Capell\PublishingStudio\Livewire\ReleaseWorkspaceSummaryPanel;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Tests\Integration\Fixtures\WorkspaceDraftableFixture;
use Capell\PublishingStudio\WorkspaceRegistry;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

uses(CreatesAdminUser::class);

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Schema::create('workspace_draftable_fixtures', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('workspace_id')->default(0)->index();
        $table->unsignedBigInteger('shadowed_by_workspace_id')->default(0)->index();
        $table->uuid('uuid');
        $table->string('name');
        $table->timestamps();
    });

    WorkspaceRegistry::reset();
    WorkspaceRegistry::register(WorkspaceDraftableFixture::class);
});

afterEach(function (): void {
    Schema::dropIfExists('workspace_draftable_fixtures');
    WorkspaceRegistry::reset();
});

it('renders release summary and readiness for release workspaces', function (): void {
    $this->actingAsAdmin();

    $workspace = Workspace::factory()->create([
        'kind' => WorkspaceKindEnum::Release,
        'status' => WorkspaceStatusEnum::Approved,
    ]);

    livewire(ReleaseWorkspaceSummaryPanel::class, ['record' => $workspace])
        ->assertSee(__('capell-admin::workspace.release.summary_title'))
        ->assertSee(__('capell-admin::workspace.release.item_count', ['count' => 0]))
        ->assertSee(__('capell-admin::workspace.release.readiness_title'))
        ->assertSee(__('capell-admin::workspace.release.blocked'));
});

it('does not expose release panel content for manual workspaces', function (): void {
    $this->actingAsAdmin();

    $workspace = Workspace::factory()->create(['kind' => WorkspaceKindEnum::Manual]);

    livewire(ReleaseWorkspaceSummaryPanel::class, ['record' => $workspace])
        ->assertDontSee(__('capell-admin::workspace.release.summary_title'));
});

it('authorizes workspace access inside the release panel component', function (): void {
    $this->actingAsUser();

    $workspace = Workspace::factory()->create(['kind' => WorkspaceKindEnum::Release]);

    livewire(ReleaseWorkspaceSummaryPanel::class, ['record' => $workspace])
        ->assertForbidden();
});

it('forbids release summaries outside the actor site scope', function (): void {
    Permission::findOrCreate('View:Workspace');

    $allowedSite = Site::factory()->create();
    $blockedSite = Site::factory()->create();
    $viewer = test()->createUserWithPermission('View:Workspace');
    $viewer->assignedSiteIds = collect([(int) $allowedSite->getKey()]);

    test()->actingAs($viewer);

    $workspace = Workspace::factory()->create([
        'kind' => WorkspaceKindEnum::Release,
        'status' => WorkspaceStatusEnum::Approved,
    ]);
    Page::factory()->create([
        'workspace_id' => $workspace->getKey(),
        'site_id' => $blockedSite->getKey(),
    ]);

    livewire(ReleaseWorkspaceSummaryPanel::class, ['record' => $workspace])
        ->assertForbidden();
});

it('renders a bounded item list with accessible metadata labels', function (): void {
    $this->actingAsAdmin();

    $workspace = Workspace::factory()->create([
        'kind' => WorkspaceKindEnum::Release,
        'status' => WorkspaceStatusEnum::Approved,
    ]);

    foreach (range(1, 30) as $itemNumber) {
        WorkspaceDraftableFixture::query()
            ->withoutGlobalScopes()
            ->create([
                'workspace_id' => $workspace->id,
                'shadowed_by_workspace_id' => 0,
                'uuid' => (string) Str::uuid(),
                'name' => sprintf('Release item %d', $itemNumber),
            ]);
    }

    livewire(ReleaseWorkspaceSummaryPanel::class, ['record' => $workspace])
        ->assertSee('Release item 1')
        ->assertDontSee('Release item 30')
        ->assertSee(__('capell-admin::workspace.release.remaining_items', ['count' => 5]))
        ->assertSee(__('capell-admin::workspace.release.item_source'))
        ->assertSee(__('capell-admin::workspace.release.item_change_type'));
});
