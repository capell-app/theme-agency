<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\Navigation\Models\Navigation;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\RebaseReport;

function makeWorkspaceForReport(?int $baseVersionId): Workspace
{
    $workspace = new Workspace;
    $workspace->forceFill([
        'id' => 1,
        'base_version_id' => $baseVersionId,
    ]);

    return $workspace;
}

it('dashboard-dashboard_reports no conflicts when the map is empty', function (): void {
    $report = new RebaseReport(makeWorkspaceForReport(1), 1, []);

    expect($report->hasConflicts())->toBeFalse()
        ->and($report->conflictCount())->toBe(0)
        ->and($report->conflicts())->toBe([]);
});

it('addConflict accumulates uuids without duplicates', function (): void {
    $report = new RebaseReport(makeWorkspaceForReport(1), 2, []);

    $report->addConflict(Page::class, 'uuid-one');
    $report->addConflict(Page::class, 'uuid-two');
    $report->addConflict(Page::class, 'uuid-one');
    $report->addConflict(Navigation::class, 'nav-uuid');

    expect($report->hasConflicts())->toBeTrue()
        ->and($report->conflictCount())->toBe(3)
        ->and($report->conflicts())->toBe([
            Page::class => ['uuid-one', 'uuid-two'],
            Navigation::class => ['nav-uuid'],
        ]);
});

it('is stale when the workspace base is behind the live version', function (): void {
    $report = new RebaseReport(makeWorkspaceForReport(1), 5, []);

    expect($report->isStale())->toBeTrue();
});

it('is not stale when the workspace is already on the live version', function (): void {
    $report = new RebaseReport(makeWorkspaceForReport(5), 5, []);

    expect($report->isStale())->toBeFalse();
});

it('is not stale when there is no live version yet', function (): void {
    $report = new RebaseReport(makeWorkspaceForReport(3), null, []);

    expect($report->isStale())->toBeFalse();
});

it('is not stale when the workspace has no base version', function (): void {
    $report = new RebaseReport(makeWorkspaceForReport(null), 2, []);

    expect($report->isStale())->toBeFalse();
});
