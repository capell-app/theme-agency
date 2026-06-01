<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\PublishingStudio\Livewire\WorkspaceApprovalHistory;
use Capell\PublishingStudio\Models\Workspace;
use Capell\Tests\Support\Concerns\CreatesAdminUser;

use function Pest\Livewire\livewire;

use Spatie\Permission\Models\Permission;

uses(CreatesAdminUser::class)->group('publishing-studio');

it('forbids approval history for workspaces outside the actor site scope', function (): void {
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

    livewire(WorkspaceApprovalHistory::class, ['record' => $workspace])
        ->assertForbidden();
});
