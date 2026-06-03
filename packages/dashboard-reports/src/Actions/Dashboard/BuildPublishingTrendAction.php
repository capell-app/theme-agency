<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Actions\Dashboard;

use Capell\Admin\Support\SiteScope;
use Capell\Core\Models\Page;
use Capell\DashboardReports\Data\Dashboard\PublishingTrendData;
use Capell\DashboardReports\Data\Dashboard\PublishingTrendPointData;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Lorisleiva\Actions\Concerns\AsObject;

final class BuildPublishingTrendAction
{
    use AsObject;

    private const int BUCKET_COUNT = 7;

    public function handle(string $period = 'last_30_days'): PublishingTrendData
    {
        [$rangeStart, $rangeEnd] = $this->resolveDateRange($period);
        $buckets = $this->buildBuckets($rangeStart, $rangeEnd);

        $page = new Page;
        $grammar = $page->getConnection()->getQueryGrammar();
        $publishedColumnExpression = 'COALESCE('
            . $grammar->wrap($page->qualifyColumn('visible_from')) . ', '
            . $grammar->wrap($page->qualifyColumn('created_at')) . ')';

        $publishedCounts = $this->bucketedCounts(
            $this->basePageQuery()->publishedDate(),
            $publishedColumnExpression,
            $buckets,
            useRawColumn: true,
        );

        $scheduledCounts = $this->bucketedCounts(
            $this->basePageQuery()->pending(),
            $page->qualifyColumn('visible_from'),
            $buckets,
            useRawColumn: false,
        );

        $points = [];

        foreach ($buckets as $index => $bucket) {
            $points[] = new PublishingTrendPointData(
                label: $bucket['start']->format('M j'),
                publishedCount: $publishedCounts[$index] ?? 0,
                scheduledCount: $scheduledCounts[$index] ?? 0,
            );
        }

        return new PublishingTrendData(
            points: $points,
            totalPublished: array_sum($publishedCounts),
            totalScheduled: $this->basePageQuery()->pending()->count(),
        );
    }

    /**
     * @return list<array{start: CarbonImmutable, end: CarbonImmutable, includeRangeEnd: bool}>
     */
    private function buildBuckets(CarbonImmutable $rangeStart, CarbonImmutable $rangeEnd): array
    {
        $bucketSeconds = max(1, (int) ($rangeStart->diffInSeconds($rangeEnd) / self::BUCKET_COUNT));
        $buckets = [];

        for ($bucket = 0; $bucket < self::BUCKET_COUNT; $bucket++) {
            $buckets[] = [
                'start' => $rangeStart->addSeconds($bucket * $bucketSeconds),
                'end' => $rangeStart->addSeconds(($bucket + 1) * $bucketSeconds),
                'includeRangeEnd' => $bucket === self::BUCKET_COUNT - 1,
            ];
        }

        return $buckets;
    }

    /**
     * Resolve all bucket counts for a series in a single grouped aggregate query using
     * conditional sums, replacing the per-bucket COUNT round-trips.
     *
     * @param  Builder<Page>  $query
     * @param  list<array{start: CarbonImmutable, end: CarbonImmutable, includeRangeEnd: bool}>  $buckets
     * @return list<int>
     */
    private function bucketedCounts(Builder $query, string $column, array $buckets, bool $useRawColumn): array
    {
        $grammar = $query->getQuery()->getGrammar();
        $wrappedColumn = $useRawColumn ? $column : $grammar->wrap($column);
        $dateFormat = $grammar->getDateFormat();

        $selects = [];
        $bindings = [];

        foreach ($buckets as $index => $bucket) {
            $upperOperator = $bucket['includeRangeEnd'] ? '<=' : '<';
            $selects[] = "SUM(CASE WHEN {$wrappedColumn} >= ? AND {$wrappedColumn} {$upperOperator} ? THEN 1 ELSE 0 END) AS bucket_{$index}";
            $bindings[] = $bucket['start']->format($dateFormat);
            $bindings[] = $bucket['end']->format($dateFormat);
        }

        $row = $query
            ->select(new Expression(implode(', ', $selects)))
            ->addBinding($bindings, 'select')
            ->first();

        $counts = [];

        foreach (array_keys($buckets) as $index) {
            $value = $row?->getAttribute("bucket_{$index}");
            $counts[$index] = $value === null ? 0 : (int) $value;
        }

        return $counts;
    }

    /** @return array{CarbonImmutable, CarbonImmutable} */
    private function resolveDateRange(string $period): array
    {
        $now = CarbonImmutable::now();

        return match ($period) {
            'today' => [$now->startOfDay(), $now->endOfDay()],
            'this_week' => [$now->startOfWeek(), $now->endOfWeek()],
            'this_month' => [$now->startOfMonth(), $now->endOfMonth()],
            'this_year' => [$now->startOfYear(), $now->endOfYear()],
            default => [$now->subDays(30)->startOfDay(), $now->endOfDay()],
        };
    }

    /**
     * @return Builder<Page>
     */
    private function basePageQuery(): Builder
    {
        /** @var Builder<Page> $query */
        $query = SiteScope::applyForCurrentActor(Page::query());

        return $query;
    }
}
