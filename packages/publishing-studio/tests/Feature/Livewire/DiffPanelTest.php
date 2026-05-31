<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\PublishingStudio\Livewire\DiffPanel;
use Capell\PublishingStudio\Models\Workspace;
use Capell\Tests\Support\Concerns\CreatesAdminUser;

use function Pest\Livewire\livewire;

use Spatie\Permission\Models\Permission;

uses()->group('publishing-studio');
uses(CreatesAdminUser::class);

beforeEach(function (): void {
    $this->actingAsAdmin();
});

it('starts in side-by-side mode with unchanged hidden', function (): void {
    $workspace = Workspace::factory()->create();

    livewire(DiffPanel::class, ['workspaceId' => $workspace->id])
        ->assertSet('mode', 'side-by-side')
        ->assertSet('showUnchanged', false);
});

it('toggleMode switches to inline then back to side-by-side', function (): void {
    $workspace = Workspace::factory()->create();

    livewire(DiffPanel::class, ['workspaceId' => $workspace->id])
        ->call('toggleMode')
        ->assertSet('mode', 'inline')
        ->call('toggleMode')
        ->assertSet('mode', 'side-by-side');
});

it('toggleUnchanged flips the flag', function (): void {
    $workspace = Workspace::factory()->create();

    livewire(DiffPanel::class, ['workspaceId' => $workspace->id])
        ->assertSet('showUnchanged', false)
        ->call('toggleUnchanged')
        ->assertSet('showUnchanged', true)
        ->call('toggleUnchanged')
        ->assertSet('showUnchanged', false);
});

it('forbids mounting a workspace from another assigned site', function (): void {
    Permission::findOrCreate('View:Workspace');

    $allowedSite = Site::factory()->create();
    $blockedSite = Site::factory()->create();
    $viewer = test()->createUserWithPermission('View:Workspace');
    $viewer->assignedSiteIds = collect([(int) $allowedSite->getKey()]);

    test()->actingAs($viewer);

    $workspace = Workspace::factory()->create();
    Page::factory()->create([
        'workspace_id' => $workspace->getKey(),
        'site_id' => $blockedSite->getKey(),
    ]);

    livewire(DiffPanel::class, ['workspaceId' => $workspace->getKey()])
        ->assertForbidden();
});
