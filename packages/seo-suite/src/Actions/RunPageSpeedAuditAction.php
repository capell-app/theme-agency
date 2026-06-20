<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Actions\GetEditPageResourceUrlAction;
use Capell\Core\Models\Page;
use Capell\SeoSuite\Contracts\PageSpeedInsightsClientInterface;
use Capell\SeoSuite\Data\PageSpeedAuditDigestFindingData;
use Capell\SeoSuite\Data\PageSpeedAuditResultData;
use Capell\SeoSuite\Data\PageSpeedAuditSummaryData;
use Capell\SeoSuite\Enums\PageSpeedAuditRunStatusEnum;
use Capell\SeoSuite\Enums\PageSpeedAuditTriggerEnum;
use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Capell\SeoSuite\Models\PageSpeedAuditResult;
use Capell\SeoSuite\Models\PageSpeedAuditRun;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class RunPageSpeedAuditAction
{
    use AsAction;

    /**
     * @param  list<PageSpeedStrategyEnum>  $strategies
     */
    public function handle(
        PageSpeedAuditTriggerEnum $trigger = PageSpeedAuditTriggerEnum::Command,
        ?int $siteId = null,
        ?int $languageId = null,
        ?int $pageId = null,
        array $strategies = [],
        ?int $limit = null,
        ?Model $requestedBy = null,
        bool $notify = false,
    ): PageSpeedAuditSummaryData {
        $strategies = $strategies !== [] ? $strategies : PageSpeedStrategyEnum::cases();
        $targets = ResolvePageSpeedAuditTargetsAction::run($siteId, $languageId, $pageId, $limit);

        $run = PageSpeedAuditRun::query()->create([
            'trigger' => $trigger->value,
            'status' => PageSpeedAuditRunStatusEnum::Running->value,
            'scope' => [
                'site_id' => $siteId,
                'language_id' => $languageId,
                'page_id' => $pageId,
                'strategies' => array_map(static fn (PageSpeedStrategyEnum $strategy): string => $strategy->value, $strategies),
                'limit' => $limit,
            ],
            'target_count' => count($targets),
            'requested_by_type' => $requestedBy?->getMorphClass(),
            'requested_by_id' => $requestedBy?->getKey(),
            'notification_status' => $notify ? 'pending' : 'not_requested',
            'started_at' => now(),
        ]);

        $successCount = 0;
        $failureCount = 0;
        $poorResults = 0;

        /** @var PageSpeedInsightsClientInterface $client */
        $client = resolve(PageSpeedInsightsClientInterface::class);

        try {
            foreach ($targets as $target) {
                foreach ($strategies as $strategy) {
                    $result = $this->analyze($client, $target->url, $strategy);

                    $model = PersistPageSpeedAuditResultAction::run(
                        run: $run,
                        page: $target->page,
                        site: $target->site,
                        language: $target->language,
                        result: $result,
                    );

                    if ($result->successful) {
                        $successCount++;

                        if (($model->performance_score ?? 100) < 50) {
                            $poorResults++;
                        }

                        continue;
                    }

                    $failureCount++;
                }
            }

            $run->update([
                'status' => $failureCount > 0
                    ? PageSpeedAuditRunStatusEnum::SucceededWithErrors->value
                    : PageSpeedAuditRunStatusEnum::Succeeded->value,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'completed_at' => now(),
            ]);
        } catch (Throwable $throwable) {
            $failureCount++;

            $run->update([
                'status' => PageSpeedAuditRunStatusEnum::Failed->value,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'error_message' => $throwable->getMessage(),
                'completed_at' => now(),
            ]);
        }

        $run->refresh();
        $runResults = $this->runResults($run);

        $summary = new PageSpeedAuditSummaryData(
            run: $run,
            auditedPages: count($targets),
            successfulResults: $successCount,
            failedResults: $failureCount,
            poorResults: $poorResults,
            worstMobileResults: $this->worstResults($runResults, PageSpeedStrategyEnum::Mobile),
            worstDesktopResults: $this->worstResults($runResults, PageSpeedStrategyEnum::Desktop),
            biggestDrops: $this->biggestDrops($runResults),
            belowThresholdResults: $this->belowThresholdResults($runResults),
        );

        if ($notify && $run->status !== PageSpeedAuditRunStatusEnum::Failed) {
            SendPageSpeedAuditDigestAction::run($summary);
        } elseif ($notify) {
            $run->update(['notification_status' => 'not_sent_failed']);
        }

        if (! $notify) {
            $this->notifyRequester($summary, $runResults, $requestedBy);
        }

        return $summary;
    }

    private function analyze(PageSpeedInsightsClientInterface $client, string $url, PageSpeedStrategyEnum $strategy): PageSpeedAuditResultData
    {
        try {
            return $client->analyze($url, $strategy);
        } catch (Throwable $throwable) {
            report($throwable);

            return new PageSpeedAuditResultData(
                strategy: $strategy,
                url: $url,
                successful: false,
                errorMessage: $throwable->getMessage(),
            );
        }
    }

    /**
     * @return Collection<int, PageSpeedAuditResult>
     */
    private function runResults(PageSpeedAuditRun $run): Collection
    {
        return PageSpeedAuditResult::query()
            ->where('page_speed_audit_run_id', $run->getKey())
            ->with('page.type')
            ->orderBy('performance_score')
            ->get();
    }

    /**
     * @param  Collection<int, PageSpeedAuditResult>  $results
     */
    private function notifyRequester(PageSpeedAuditSummaryData $summary, Collection $results, ?Model $requestedBy): void
    {
        if (! $requestedBy instanceof Authenticatable || ! Schema::hasTable('notifications')) {
            return;
        }

        $notification = Notification::make('seo-suite-pagespeed-audit-complete-' . $summary->run->getKey())
            ->title(__('capell-seo-suite::generic.pagespeed_complete_title'))
            ->body(__('capell-seo-suite::generic.pagespeed_complete_body', [
                'pages' => $summary->auditedPages,
                'average' => $this->averagePerformanceScoreLabel($results),
                'failed' => $summary->failedResults,
            ]))
            ->icon(Heroicon::OutlinedBolt)
            ->success()
            ->persistent()
            ->actions($this->lowRatedPageActions($results));

        try {
            $notification->broadcast($requestedBy);
            $notification->sendToDatabase($requestedBy);
        } catch (Throwable $throwable) {
            report($throwable);
        }
    }

    /**
     * @param  Collection<int, PageSpeedAuditResult>  $results
     */
    private function averagePerformanceScoreLabel(Collection $results): string
    {
        $average = $results
            ->filter(fn (PageSpeedAuditResult $result): bool => $result->status === 'succeeded'
                && $result->performance_score !== null)
            ->avg('performance_score');

        if ($average === null) {
            return (string) __('capell-seo-suite::generic.pagespeed_average_unavailable');
        }

        return (string) round((float) $average);
    }

    /**
     * @param  Collection<int, PageSpeedAuditResult>  $results
     * @return list<Action>
     */
    private function lowRatedPageActions(Collection $results): array
    {
        return $results
            ->filter(fn (PageSpeedAuditResult $result): bool => $result->status === 'succeeded'
                && $result->performance_score !== null
                && $result->performance_score < 50)
            ->sortBy('performance_score')
            ->unique('page_id')
            ->take(3)
            ->map(function (PageSpeedAuditResult $result): ?Action {
                $page = $result->page;

                if (! $page instanceof Page) {
                    return null;
                }

                $url = GetEditPageResourceUrlAction::run($page);

                if ($url === null) {
                    return null;
                }

                return Action::make('openLowRatedPage' . $page->getKey())
                    ->label(__('capell-seo-suite::generic.pagespeed_open_low_rated_page', [
                        'page' => $page->name,
                        'score' => $result->performance_score,
                    ]))
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->link()
                    ->close()
                    ->url($url);
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, PageSpeedAuditResult>  $results
     * @return list<PageSpeedAuditDigestFindingData>
     */
    private function worstResults(Collection $results, PageSpeedStrategyEnum $strategy): array
    {
        return array_values($results
            ->filter(fn (PageSpeedAuditResult $result): bool => $result->status === 'succeeded'
                && $result->strategy === $strategy
                && $result->performance_score !== null)
            ->sortBy('performance_score')
            ->take(5)
            ->map(fn (PageSpeedAuditResult $result): PageSpeedAuditDigestFindingData => $this->finding($result))
            ->values()
            ->all());
    }

    /**
     * @param  Collection<int, PageSpeedAuditResult>  $results
     * @return list<PageSpeedAuditDigestFindingData>
     */
    private function belowThresholdResults(Collection $results): array
    {
        return array_values($results
            ->filter(fn (PageSpeedAuditResult $result): bool => $result->status === 'succeeded'
                && $result->performance_score !== null
                && $result->performance_score < 50)
            ->sortBy('performance_score')
            ->take(10)
            ->map(fn (PageSpeedAuditResult $result): PageSpeedAuditDigestFindingData => $this->finding($result))
            ->values()
            ->all());
    }

    /**
     * @param  Collection<int, PageSpeedAuditResult>  $results
     * @return list<PageSpeedAuditDigestFindingData>
     */
    private function biggestDrops(Collection $results): array
    {
        return array_values($results
            ->filter(fn (PageSpeedAuditResult $result): bool => $result->status === 'succeeded'
                && $result->performance_score !== null)
            ->map(function (PageSpeedAuditResult $result): ?PageSpeedAuditDigestFindingData {
                $previous = PageSpeedAuditResult::query()
                    ->where('page_id', $result->page_id)
                    ->where('language_id', $result->language_id)
                    ->where('strategy', $result->strategyEnum()->value)
                    ->where('status', 'succeeded')
                    ->where('id', '<>', $result->getKey())
                    ->where('fetched_at', '<', $result->fetched_at)
                    ->whereNotNull('performance_score')
                    ->latest('fetched_at')
                    ->latest('id')
                    ->first();

                if (! $previous instanceof PageSpeedAuditResult || $previous->performance_score === null) {
                    return null;
                }

                $drop = $previous->performance_score - $result->performance_score;

                if ($drop <= 0) {
                    return null;
                }

                return $this->finding($result, $previous->performance_score, $drop);
            })
            ->filter()
            ->sortByDesc(fn (PageSpeedAuditDigestFindingData $finding): int => $finding->drop ?? 0)
            ->take(5)
            ->values()
            ->all());
    }

    private function finding(PageSpeedAuditResult $result, ?int $previousScore = null, ?int $drop = null): PageSpeedAuditDigestFindingData
    {
        return new PageSpeedAuditDigestFindingData(
            url: $result->url,
            strategy: $result->strategyEnum(),
            score: $result->performance_score,
            previousScore: $previousScore,
            drop: $drop,
        );
    }
}
