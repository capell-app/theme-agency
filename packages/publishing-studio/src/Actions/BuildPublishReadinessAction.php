<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions;

use Capell\PublishingStudio\Checks\PublishCheckResult;
use Capell\PublishingStudio\Data\PublishReadinessData;
use Capell\PublishingStudio\DryRunReport;
use Capell\PublishingStudio\Exceptions\StaleWorkspaceException;
use Capell\PublishingStudio\Exceptions\UrlCollisionException;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Publisher;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static PublishReadinessData run(Workspace $workspace, bool $canBypassReleaseWindow = false)
 */
final class BuildPublishReadinessAction
{
    use AsObject;

    public function handle(Workspace $workspace, bool $canBypassReleaseWindow = false): PublishReadinessData
    {
        $report = resolve(Publisher::class)->dryRun($workspace, bypassWindow: $canBypassReleaseWindow);
        $blockingIssues = $this->blockingIssues($report);

        return new PublishReadinessData(
            workspaceId: $this->workspaceId($workspace),
            wouldPublish: $report->wouldPublish && $blockingIssues === [],
            totalRows: $report->totalRows(),
            rowCounts: $report->rowCounts,
            collisions: $report->collisions,
            conflictCount: $report->rebaseReport?->conflictCount() ?? 0,
            checkResults: $report->checkResults,
            failureMessage: $this->failureMessage($report),
            blockingIssues: $blockingIssues,
            blockingIssueCount: count($blockingIssues),
        );
    }

    /**
     * @return list<string>
     */
    private function blockingIssues(DryRunReport $report): array
    {
        $blockingIssues = [];
        $failureMessage = $this->failureMessage($report);

        if ($failureMessage !== null) {
            $blockingIssues[] = $failureMessage;
        }

        foreach ($report->collisions as $collision) {
            $blockingIssues[] = __('capell-admin::workspace.release.blocking.url_collision', [
                'url' => $collision['url'],
            ]);
        }

        if ($report->hasConflicts()) {
            $blockingIssues[] = __('capell-admin::workspace.release.blocking.stale_conflicts', [
                'count' => $report->rebaseReport?->conflictCount() ?? 0,
            ]);
        }

        foreach ($report->checkResults as $checkResult) {
            if (! $checkResult instanceof PublishCheckResult) {
                continue;
            }

            if (! $checkResult->isError() || $checkResult->isClean()) {
                continue;
            }

            array_push(
                $blockingIssues,
                ...($checkResult->messages === [] ? [$checkResult->label] : $checkResult->messages),
            );
        }

        return array_values(array_unique(array_filter(
            $blockingIssues,
            static fn (string $blockingIssue): bool => $blockingIssue !== '',
        )));
    }

    private function failureMessage(DryRunReport $report): ?string
    {
        if ($report->failure === null) {
            return null;
        }

        if ($report->failure instanceof UrlCollisionException && $report->collisions !== []) {
            return null;
        }

        if ($report->failure instanceof StaleWorkspaceException && $report->hasConflicts()) {
            return null;
        }

        $message = $report->failure->getMessage();

        return $message === '' ? null : $message;
    }

    private function workspaceId(Workspace $workspace): int
    {
        $key = $workspace->getKey();

        return is_numeric($key) ? (int) $key : 0;
    }
}
