<?php

declare(strict_types=1);

use Capell\Admin\Filament\Resources\Pages\Pages\EditPage;
use Capell\Core\Models\Page;
use Capell\PublishingStudio\Actions\CopyOnWriteAction;
use Capell\PublishingStudio\Enums\WorkspaceKindEnum;
use Capell\PublishingStudio\Enums\WorkspaceStatusEnum;
use Capell\PublishingStudio\Facades\CapellPublishingStudio;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Support\PublishingStudioManager;
use Capell\PublishingStudio\WorkspaceContext;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(CreatesAdminUser::class);

beforeEach(function (): void {
    config()->set('capell.publishing-studio.release_windows.enabled', false);
    app()->forgetInstance(PublishingStudioManager::class);
    CapellPublishingStudio::clearResolvedInstance(PublishingStudioManager::class);

    Role::findOrCreate('super_admin');
    $adminUser = $this->createUser();
    $adminUser->assignRole('super_admin');
    $this->actingAs($adminUser);
});

it('runs the full live -> draft -> publish cycle', function (): void {
    $page = Page::factory()->withTranslations()->create(['name' => 'About']);

    // Step 1: save as draft (new workspace)
    Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
        ->callAction('saveAsDraft', data: ['location' => 'new'])
        ->assertHasNoActionErrors();

    $draft = Page::query()->withoutGlobalScopes()
        ->where('uuid', $page->uuid)
        ->where('workspace_id', '>', 0)
        ->first();

    $draft = publishingStudioTestInstance($draft, Page::class);
    $workspace = Workspace::query()
        ->whereKey($draft->workspace_id)
        ->where('kind', WorkspaceKindEnum::SinglePageDraft)
        ->first();

    $workspace = publishingStudioTestInstance($workspace, Workspace::class);

    // The Publisher only accepts Approved/Scheduled. A freshly saved draft
    // starts in Open — approve it here so publish can proceed. In the real UI
    // this happens via the approval flow (see Task 5).
    $workspace->update(['status' => WorkspaceStatusEnum::Approved]);

    // Step 2: publish the draft
    $workspace->runInContext(function () use ($draft): void {
        Livewire::test(EditPage::class, ['record' => $draft->getRouteKey()])
            ->assertActionVisible('publish')
            ->callAction('publish')
            ->assertNotified();
    });

    // Step 3: the page should now be live, the workspace published
    $freshWorkspace = publishingStudioTestInstance($workspace->fresh(), Workspace::class);

    expect(Page::query()->where('uuid', $page->uuid)->where('workspace_id', 0)->exists())->toBeTrue()
        ->and($freshWorkspace->status)->toBe(WorkspaceStatusEnum::Published);
});

it('disables publish while in review and enables after approval', function (): void {
    $live = Page::factory()->withTranslations()->create();
    $workspace = Workspace::factory()->create(['status' => WorkspaceStatusEnum::InReview]);
    $freshLive = publishingStudioTestInstance($live->fresh(), Page::class);
    $draft = publishingStudioTestInstance((new CopyOnWriteAction)->cloneForEdit(
        $freshLive->fill(['name' => 'updated']),
        $workspace,
    ), Page::class);

    $workspace->runInContext(function () use ($draft): void {
        Livewire::test(EditPage::class, ['record' => $draft->getRouteKey()])
            ->assertActionDisabled('publish');
    });

    $workspace->update(['status' => WorkspaceStatusEnum::Approved]);

    WorkspaceContext::set($workspace->fresh());

    try {
        $freshDraft = publishingStudioTestInstance($draft->fresh(), Page::class);

        Livewire::test(EditPage::class, ['record' => $freshDraft->getRouteKey()])
            ->assertActionVisible('publish')
            ->assertActionEnabled('publish');
    } finally {
        WorkspaceContext::set(null);
    }
});
