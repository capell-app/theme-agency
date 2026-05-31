<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\DashboardReports\Actions\Dashboard\BuildPublishingTrendAction;
use Capell\DashboardReports\Data\Dashboard\PublishingTrendPointData;
use Capell\DashboardReports\Tests\DashboardReportsTestCase;
use Carbon\CarbonImmutable;

uses(DashboardReportsTestCase::class);

it('builds a publishing trend series for the selected dashboard period', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-03 12:00:00'));

    Page::factory()->published(CarbonImmutable::parse('2026-05-01 09:00:00'))->create();
    Page::factory()->published(CarbonImmutable::parse('2026-05-02 09:00:00'))->create();
    Page::factory()->pending()->create();

    $data = BuildPublishingTrendAction::run('this_week');

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

    $data = BuildPublishingTrendAction::run('this_week');
    $publishedCount = array_reduce(
        $data->points,
        fn (int $total, PublishingTrendPointData $point): int => $total + $point->publishedCount,
        0,
    );

    expect($data->totalPublished)->toBe(1)
        ->and($publishedCount)->toBe(1);
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

    $data = BuildPublishingTrendAction::run('this_week');
    $scheduledCount = array_reduce(
        $data->points,
        fn (int $total, PublishingTrendPointData $point): int => $total + $point->scheduledCount,
        0,
    );

    expect($data->totalScheduled)->toBe(1)
        ->and($scheduledCount)->toBe(1);
});
