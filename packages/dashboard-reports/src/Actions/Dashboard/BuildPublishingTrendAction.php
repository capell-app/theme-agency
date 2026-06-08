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

/**
 * @method static PublishingTrendData run(CarbonImmutable $rangeStart, CarbonImmutable $rangeEnd)
 */
final class BuildPublishingTrendAction
{
    use AsObject;

    private const int BUCKET_COUNT = 7;

    public function handle(CarbonImmutable $rangeStart, CarbonImmutable $rangeEnd): PublishingTrendData
    {
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
                label: $this->bucketLabel($bucket['start'], $bucket['end']),
                publishedCount: $publishedCounts[$index] ?? 0,
                scheduledCount: $scheduledCounts[$index] ?? 0,
            );
        }

        return new PublishingTrendData(
            points: $points,
            totalPublished: array_sum($publishedCounts),
            totalScheduled: array_sum($scheduledCounts),
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

    private function bucketLabel(CarbonImmutable $start, CarbonImmutable $end): string
    {
        $displayEnd = $end->subSecond();

        if ($start->isSameDay($displayEnd)) {
            return $start->format('M j');
        }

        if ($start->isSameMonth($displayEnd)) {
            return sprintf('%s - %s', $start->format('M j'), $displayEnd->format('j'));
        }

        return sprintf('%s - %s', $start->format('M j'), $displayEnd->format('M j'));
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
            $selects[] = sprintf('SUM(CASE WHEN %s >= ? AND %s %s ? THEN 1 ELSE 0 END) AS bucket_%d', $wrappedColumn, $wrappedColumn, $upperOperator, $index);
            $bindings[] = $bucket['start']->format($dateFormat);
            $bindings[] = $bucket['end']->format($dateFormat);
        }

        $row = $query
            ->select(new Expression(implode(', ', $selects)))
            ->addBinding($bindings, 'select')
            ->first();

        $counts = [];

        foreach (array_keys($buckets) as $index) {
            $value = $row?->getAttribute('bucket_' . $index);
            $counts[$index] = $value === null ? 0 : (int) $value;
        }

        return array_values($counts);
    }

    /**
     * @return Builder<Page>
     */
    private function basePageQuery(): Builder
    {
        /** @var Builder<Page> $query */
        $query = SiteScope::applyForCurrentActor(Page::query(), denyWhenMissingActor: true);

        return $query;
    }
}
