<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Actions\Dashboard;

use Capell\Admin\Support\SiteScope;
use Capell\Core\Models\Page;
use Capell\DashboardReports\Data\Dashboard\PublishingTrendData;
use Capell\DashboardReports\Data\Dashboard\PublishingTrendPointData;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsObject;

final class BuildPublishingTrendAction
{
    use AsObject;

    public function handle(string $period = 'last_30_days'): PublishingTrendData
    {
        [$rangeStart, $rangeEnd] = $this->resolveDateRange($period);
        $bucketSeconds = max(1, (int) ($rangeStart->diffInSeconds($rangeEnd) / 7));
        $points = [];

        for ($bucket = 0; $bucket < 7; $bucket++) {
            $bucketStart = $rangeStart->addSeconds($bucket * $bucketSeconds);
            $bucketEnd = $rangeStart->addSeconds(($bucket + 1) * $bucketSeconds);
            $includeRangeEnd = $bucket === 6;

            $points[] = new PublishingTrendPointData(
                label: $bucketStart->format('M j'),
                publishedCount: $this->publishedWithin($bucketStart, $bucketEnd, $includeRangeEnd),
                scheduledCount: $this->scheduledWithin($bucketStart, $bucketEnd, $includeRangeEnd),
            );
        }

        return new PublishingTrendData(
            points: $points,
            totalPublished: collect($points)->sum(fn (PublishingTrendPointData $point): int => $point->publishedCount),
            totalScheduled: $this->basePageQuery()->pending()->count(),
        );
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

    private function publishedWithin(CarbonImmutable $rangeStart, CarbonImmutable $rangeEnd, bool $includeRangeEnd): int
    {
        return $this->basePageQuery()
            ->publishedDate()
            ->where(fn (Builder $query): Builder => $this->publishedMarkerWithin($query, $rangeStart, $rangeEnd, $includeRangeEnd))
            ->count();
    }

    private function scheduledWithin(CarbonImmutable $rangeStart, CarbonImmutable $rangeEnd, bool $includeRangeEnd): int
    {
        return $this->basePageQuery()
            ->pending()
            ->where(fn (Builder $query): Builder => $this->timestampWithin(
                $query,
                (new Page)->qualifyColumn('visible_from'),
                $rangeStart,
                $rangeEnd,
                $includeRangeEnd,
            ))
            ->count();
    }

    /**
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    private function publishedMarkerWithin(Builder $query, CarbonImmutable $rangeStart, CarbonImmutable $rangeEnd, bool $includeRangeEnd): Builder
    {
        return $query
            ->where(fn (Builder $query): Builder => $this->timestampWithin(
                $query,
                $query->getModel()->qualifyColumn('visible_from'),
                $rangeStart,
                $rangeEnd,
                $includeRangeEnd,
            ))
            ->orWhere(function (Builder $fallbackQuery) use ($rangeStart, $rangeEnd, $includeRangeEnd): void {
                $fallbackQuery
                    ->whereNull($fallbackQuery->getModel()->qualifyColumn('visible_from'))
                    ->where(fn (Builder $query): Builder => $this->timestampWithin(
                        $query,
                        $fallbackQuery->getModel()->qualifyColumn('created_at'),
                        $rangeStart,
                        $rangeEnd,
                        $includeRangeEnd,
                    ));
            });
    }

    /**
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    private function timestampWithin(Builder $query, string $column, CarbonImmutable $rangeStart, CarbonImmutable $rangeEnd, bool $includeRangeEnd): Builder
    {
        return $query
            ->where($column, '>=', $rangeStart)
            ->where($column, $includeRangeEnd ? '<=' : '<', $rangeEnd);
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
