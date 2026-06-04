<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\DashboardReports\Actions\Dashboard\BuildPublishingTrendAction;
use Capell\DashboardReports\Data\Dashboard\PublishingTrendPointData;
use Capell\DashboardReports\Tests\DashboardReportsTestCase;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

uses(DashboardReportsTestCase::class);

/**
 * @return array{CarbonImmutable, CarbonImmutable}
 */
function dashboardReportsThisWeekRange(): array
{
    return [
        CarbonImmutable::now()->startOfWeek(),
        CarbonImmutable::now()->endOfWeek(),
    ];
}

it('builds a publishing trend series for the selected dashboard period', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-03 12:00:00'));

    Page::factory()->published(CarbonImmutable::parse('2026-05-01 09:00:00'))->create();
    Page::factory()->published(CarbonImmutable::parse('2026-05-02 09:00:00'))->create();
    Page::factory()->pending()->create([
        'visible_from' => CarbonImmutable::parse('2026-05-03 18:00:00'),
    ]);

    $data = BuildPublishingTrendAction::run(...dashboardReportsThisWeekRange());

    expect($data->points)->toHaveCount(7)
        ->and($data->totalPublished)->toBe(2)
        ->and($data->totalScheduled)->toBe(1);
});

it('counts pages on publishing trend bucket boundaries once', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-03 12:00:00'));

    $rangeStart = CarbonImmutable::now()->startOfWeek();
    $rangeEnd = CarbonImmutable::now()->endOfWeek();
    $bucketSeconds = max(1, (int) ($rangeStart->diffInSeconds($rangeEnd) / 7));
    $firstBoundary = $rangeStart->addSeconds($bucketSeconds);

    Page::factory()->published($firstBoundary)->create();

    $data = BuildPublishingTrendAction::run(...dashboardReportsThisWeekRange());
    $publishedCount = array_reduce(
        $data->points,
        fn (int $total, PublishingTrendPointData $point): int => $total + $point->publishedCount,
        0,
    );

    expect($data->totalPublished)->toBe(1)
        ->and($publishedCount)->toBe(1);
});

it('resolves the publishing trend in a bounded number of grouped queries', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-03 12:00:00'));

    Page::factory()->published(CarbonImmutable::parse('2026-05-01 09:00:00'))->create();
    Page::factory()->pending()->create();

    DB::enableQueryLog();

    BuildPublishingTrendAction::run(...dashboardReportsThisWeekRange());

    $selectQueries = array_filter(
        DB::getQueryLog(),
        fn (array $entry): bool => str_starts_with(strtolower(ltrim((string) $entry['query'])), 'select'),
    );

    DB::disableQueryLog();

    // Two grouped bucket aggregates (published + scheduled), instead of the previous
    // 15 per-bucket COUNT round-trips and all-window scheduled total.
    expect(count($selectQueries))->toBeLessThanOrEqual(2);
});

it('counts scheduled pages on publishing trend bucket boundaries once', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-04-27 00:00:00'));

    $rangeStart = CarbonImmutable::now()->startOfWeek();
    $rangeEnd = CarbonImmutable::now()->endOfWeek();
    $bucketSeconds = max(1, (int) ($rangeStart->diffInSeconds($rangeEnd) / 7));
    $firstBoundary = $rangeStart->addSeconds($bucketSeconds);

    Page::factory()->pending()->create([
        'visible_from' => $firstBoundary,
    ]);

    $data = BuildPublishingTrendAction::run(...dashboardReportsThisWeekRange());
    $scheduledCount = array_reduce(
        $data->points,
        fn (int $total, PublishingTrendPointData $point): int => $total + $point->scheduledCount,
        0,
    );

    expect($data->totalScheduled)->toBe(1)
        ->and($scheduledCount)->toBe(1);
});

it('keeps the scheduled total scoped to the selected dashboard range', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-03 12:00:00'));

    Page::factory()->pending()->create([
        'visible_from' => CarbonImmutable::parse('2026-05-03 18:00:00'),
    ]);
    Page::factory()->pending()->create([
        'visible_from' => CarbonImmutable::parse('2026-08-01 09:00:00'),
    ]);

    $data = BuildPublishingTrendAction::run(...dashboardReportsThisWeekRange());

    expect($data->totalScheduled)->toBe(1);
});
