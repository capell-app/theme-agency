<?php

declare(strict_types=1);

use Capell\Admin\Contracts\Dashboard\ContentHealthDataProvider;
use Capell\Admin\Contracts\Dashboard\MyWorkQueueDataProvider;
use Capell\Admin\Contracts\Dashboard\RecentlyPublishedDataProvider;
use Capell\Admin\Contracts\Dashboard\SiteStatsDataProvider;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Translation;
use Capell\PublishingStudio\Actions\Dashboard\BuildContentHealthAction;
use Capell\PublishingStudio\Actions\Dashboard\BuildMyWorkQueueAction;
use Capell\PublishingStudio\Actions\Dashboard\BuildRecentlyPublishedAction;
use Capell\PublishingStudio\Actions\Dashboard\BuildSiteStatsAction;
use Capell\PublishingStudio\Models\Version;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Models\WorkspaceReviewAssignment;
use Capell\PublishingStudio\Support\Dashboard\WorkspaceContentHealthDataProvider;
use Capell\PublishingStudio\Support\Dashboard\WorkspaceMyWorkQueueDataProvider;
use Capell\PublishingStudio\Support\Dashboard\WorkspaceRecentlyPublishedDataProvider;
use Capell\PublishingStudio\Support\Dashboard\WorkspaceSiteStatsDataProvider;
use Illuminate\Support\Str;

it('covers publishing dashboard data providers and their action branches', function (): void {
    $owner = $this->createUser();
    $reviewer = $this->createUser();
    $site = Site::factory()->withTranslations()->create(['name' => 'Main Site']);

    $publishedWorkspace = Workspace::factory()->published()->create(['published_at' => now()->subDay()]);
    $draftWorkspace = Workspace::factory()->open()->create(['created_by' => $owner->id]);
    $reviewWorkspace = Workspace::factory()->inReview()->create();
    $scheduledWorkspace = Workspace::factory()->scheduled(now()->addDays(2))->create(['created_by' => $owner->id]);

    $publishedPage = Page::factory()->site($site)->create([
        'workspace_id' => $publishedWorkspace->id,
        'updated_at' => now()->subDays(120),
    ]);
    $draftPage = Page::factory()->create(['workspace_id' => $draftWorkspace->id]);
    $reviewPage = Page::factory()->create(['workspace_id' => $reviewWorkspace->id]);
    $scheduledPage = Page::factory()->create(['workspace_id' => $scheduledWorkspace->id]);

    Translation::factory()
        ->translatable($publishedPage)
        ->language($site->language)
        ->create([
            'title' => 'Home',
            'meta' => ['description' => ''],
        ]);

    WorkspaceReviewAssignment::factory()->create([
        'workspace_id' => $reviewWorkspace->id,
        'reviewer_type' => $reviewer->getMorphClass(),
        'reviewer_id' => $reviewer->id,
        'decision' => null,
    ]);

    Version::query()->create([
        'uuid' => (string) Str::uuid(),
        'number' => 99,
        'name' => 'Dashboard publish',
        'manifest' => [Page::class => [$publishedPage->id]],
        'source_workspace_id' => $publishedWorkspace->id,
        'published_at' => now()->subDay(),
        'published_by_type' => $owner->getMorphClass(),
        'published_by_id' => $owner->id,
    ]);

    $contentHealth = BuildContentHealthAction::run(site: $site, staleDays: 90);
    $workQueue = BuildMyWorkQueueAction::run($owner, limit: 10, scheduledDays: 7);
    $reviewQueue = BuildMyWorkQueueAction::run($reviewer, limit: 10, scheduledDays: 7);
    $recent = BuildRecentlyPublishedAction::run(limit: 5, site: $site);
    $stats = BuildSiteStatsAction::run('this_month');

    expect(resolve(ContentHealthDataProvider::class))->toBeInstanceOf(WorkspaceContentHealthDataProvider::class)
        ->and(resolve(MyWorkQueueDataProvider::class))->toBeInstanceOf(WorkspaceMyWorkQueueDataProvider::class)
        ->and(resolve(RecentlyPublishedDataProvider::class))->toBeInstanceOf(WorkspaceRecentlyPublishedDataProvider::class)
        ->and(resolve(SiteStatsDataProvider::class))->toBeInstanceOf(WorkspaceSiteStatsDataProvider::class)
        ->and($contentHealth->issues->toCollection()->firstWhere('id', 'missing_meta')->count)->toBe(1)
        ->and($contentHealth->issues->toCollection()->firstWhere('id', 'stale')->count)->toBe(1)
        ->and($workQueue->items->toCollection()->pluck('pageId'))->toContain($draftPage->id, $scheduledPage->id)
        ->and($reviewQueue->items->toCollection()->pluck('pageId'))->toContain($reviewPage->id)
        ->and($recent->items->toCollection()->pluck('pageId'))->toContain($publishedPage->id)
        ->and($stats->publishedCount)->toBeGreaterThanOrEqual(1)
        ->and($stats->sparklinePublished)->toHaveCount(7)
        ->and($stats->pendingCount)->toBe(1);
});

it('returns empty dashboard datasets when no publishing records match', function (): void {
    $user = $this->createUser();

    expect(BuildMyWorkQueueAction::run($user)->items->count())->toBe(0)
        ->and(BuildRecentlyPublishedAction::run()->items->count())->toBe(0)
        ->and(BuildContentHealthAction::run()->issues->toCollection()->pluck('count')->all())->toBe([0, 0, 0, 0])
        ->and(BuildSiteStatsAction::run('today')->workQueueCount)->toBe(0);
});
