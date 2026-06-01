<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\PublishingStudio\Livewire\PublishStatusPanel;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\WorkspaceContext;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Carbon\CarbonImmutable;
use Livewire\Livewire;

uses(CreatesAdminUser::class);

beforeEach(function (): void {
    $this->actingAsAdmin();
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 12:00:00', 'UTC'));
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('shows published and scheduled unpublish copy', function (): void {
    $page = Page::factory()->create([
        'visible_from' => CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'),
        'visible_until' => CarbonImmutable::parse('2026-06-15 17:00:00', 'UTC'),
    ]);

    Livewire::test(PublishStatusPanel::class, ['pageId' => $page->getKey()])
        ->assertSee('Published on')
        ->assertSee('01/05/2026 09:00')
        ->assertSee('Will unpublish')
        ->assertSee('15/06/2026 17:00');
});

it('shows draft workspace state', function (): void {
    $workspace = Workspace::factory()->create(['name' => 'Sprint 2']);
    $page = Page::factory()->create([
        'workspace_id' => $workspace->id,
        'visible_from' => CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'),
    ]);

    WorkspaceContext::runWith($workspace, function () use ($page): void {
        Livewire::test(PublishStatusPanel::class, ['pageId' => $page->getKey()])
            ->assertSee('Draft in Sprint 2')
            ->assertSee('Workspace')
            ->assertSee('Sprint 2');
    });
});
