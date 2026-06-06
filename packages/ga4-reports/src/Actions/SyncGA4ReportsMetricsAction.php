<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Actions;

use Capell\GA4Reports\Contracts\GA4ReportsDataClientInterface;
use Capell\GA4Reports\Data\GA4ReportsSyncResultData;
use Capell\GA4Reports\Data\GA4ReportsWindowData;
use Capell\GA4Reports\Models\GA4ReportsSyncRun;
use Capell\GA4Reports\Support\GA4ReportsDashboardCache;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class SyncGA4ReportsMetricsAction
{
    use AsAction;

    public function handle(): GA4ReportsSyncResultData
    {
        $window = BuildGA4ReportsWindowAction::run();

        if ($window === null) {
            return new GA4ReportsSyncResultData(false, __('capell-ga4-reports::sync.not_configured'));
        }

        /** @var GA4ReportsDataClientInterface $client */
        $client = resolve(GA4ReportsDataClientInterface::class);

        if (! $client->isConfigured()) {
            return new GA4ReportsSyncResultData(false, __('capell-ga4-reports::sync.not_configured'));
        }

        $lock = $this->acquireLock($window);

        if (! $lock instanceof Lock) {
            return new GA4ReportsSyncResultData(false, __('capell-ga4-reports::sync.already_running'));
        }

        $syncRun = GA4ReportsSyncRun::query()->create([
            'property_id' => $window->propertyId,
            'status' => 'running',
            'window_start' => $window->startsAt->toDateString(),
            'window_end' => $window->endsAt->toDateString(),
            'started_at' => Date::now(),
        ]);

        try {
            $dailyMetrics = $client->dailyMetrics($window);
            $pageMetrics = $client->pageMetrics($window);

            foreach ($dailyMetrics as $dailyMetric) {
                PersistGA4ReportsDailyMetricAction::run($dailyMetric);
            }

            foreach ($pageMetrics as $pageMetric) {
                PersistGA4ReportsPageMetricAction::run($pageMetric);
            }

            $syncRun->update([
                'status' => 'succeeded',
                'daily_rows' => count($dailyMetrics),
                'page_rows' => count($pageMetrics),
                'finished_at' => Date::now(),
            ]);

            GA4ReportsDashboardCache::flushForWindow($window);

            return new GA4ReportsSyncResultData(
                synced: true,
                message: __('capell-ga4-reports::sync.synced'),
                dailyRows: count($dailyMetrics),
                pageRows: count($pageMetrics),
            );
        } catch (Throwable $throwable) {
            $syncRun->update([
                'status' => 'failed',
                'error_message' => $throwable->getMessage(),
                'finished_at' => Date::now(),
            ]);

            return new GA4ReportsSyncResultData(false, __('capell-ga4-reports::sync.failed'));
        } finally {
            $lock->release();
        }
    }

    private function acquireLock(GA4ReportsWindowData $window): ?Lock
    {
        $seconds = config('capell-ga4-reports.sync_lock_seconds', 3600);
        $lockSeconds = is_numeric($seconds) ? max(60, (int) $seconds) : 3600;
        $lock = Cache::lock($this->lockKey($window), $lockSeconds);

        return $lock->get() ? $lock : null;
    }

    private function lockKey(GA4ReportsWindowData $window): string
    {
        return implode(':', [
            'capell-ga4-reports',
            'sync',
            $window->propertyId,
            $window->startsAt->toDateString(),
            $window->endsAt->toDateString(),
        ]);
    }
}
