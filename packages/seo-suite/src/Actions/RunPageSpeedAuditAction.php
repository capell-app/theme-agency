<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\SeoSuite\Contracts\PageSpeedInsightsClientInterface;
use Capell\SeoSuite\Data\PageSpeedAuditResultData;
use Capell\SeoSuite\Data\PageSpeedAuditSummaryData;
use Capell\SeoSuite\Enums\PageSpeedAuditRunStatusEnum;
use Capell\SeoSuite\Enums\PageSpeedAuditTriggerEnum;
use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Capell\SeoSuite\Models\PageSpeedAuditRun;
use Illuminate\Database\Eloquent\Model;
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
            $run->update([
                'status' => PageSpeedAuditRunStatusEnum::Failed->value,
                'success_count' => $successCount,
                'failure_count' => $failureCount + 1,
                'error_message' => $throwable->getMessage(),
                'completed_at' => now(),
            ]);
        }

        $run->refresh();

        $summary = new PageSpeedAuditSummaryData(
            run: $run,
            auditedPages: count($targets),
            successfulResults: $successCount,
            failedResults: $failureCount,
            poorResults: $poorResults,
        );

        if ($notify) {
            SendPageSpeedAuditDigestAction::run($summary);
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
}
