<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Data\PageSpeedAuditItemData;
use Capell\SeoSuite\Data\PageSpeedAuditResultData;
use Capell\SeoSuite\Models\PageSpeedAuditResult;
use Capell\SeoSuite\Models\PageSpeedAuditRun;
use Lorisleiva\Actions\Concerns\AsAction;

final class PersistPageSpeedAuditResultAction
{
    use AsAction;

    public function handle(
        PageSpeedAuditRun $run,
        Page $page,
        Site $site,
        Language $language,
        PageSpeedAuditResultData $result,
    ): PageSpeedAuditResult {
        return PageSpeedAuditResult::query()->create([
            'page_speed_audit_run_id' => $run->getKey(),
            'page_id' => $page->getKey(),
            'site_id' => $site->getKey(),
            'language_id' => $language->getKey(),
            'url' => $result->url,
            'strategy' => $result->strategy->value,
            'status' => $result->successful ? 'succeeded' : 'failed',
            'performance_score' => $result->categoryScores['performance'] ?? null,
            'accessibility_score' => $result->categoryScores['accessibility'] ?? null,
            'best_practices_score' => $result->categoryScores['best-practices'] ?? null,
            'seo_score' => $result->categoryScores['seo'] ?? null,
            'metrics' => $result->metrics,
            'opportunities' => array_map(
                static fn (PageSpeedAuditItemData $item): array => $item->compact(),
                $result->opportunities,
            ),
            'diagnostics' => array_map(
                static fn (PageSpeedAuditItemData $item): array => $item->compact(),
                $result->diagnostics,
            ),
            'lighthouse_version' => $result->lighthouseVersion,
            'error_message' => $result->errorMessage,
            'fetched_at' => $result->fetchedAt ?? now(),
        ]);
    }
}
