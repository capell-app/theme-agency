<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\DashboardReports\Actions\Dashboard\BuildPublishingTrendAction;
use Capell\DashboardReports\Data\Dashboard\PublishingTrendPointData;
use Capell\DashboardReports\Tests\DashboardReportsTestCase;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

uses(DashboardReportsTestCase::class, CreatesAdminUser::class);

beforeEach(function (): void {
    Role::findOrCreate(dashboardReportsSuperAdminRole());
    $this->actingAsAdmin();
});

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

function dashboardReportsSuperAdminRole(): string
{
    $role = config('capell.roles.super_admin', 'super_admin');

    return is_string($role) && $role !== '' ? $role : 'super_admin';
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

it('maps grouped publishing trend aggregates back to the expected buckets', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-06 12:00:00'));

    $rangeStart = CarbonImmutable::parse('2026-05-03 00:00:00');
    $rangeEnd = CarbonImmutable::parse('2026-05-10 00:00:00');

    Page::factory()->published(CarbonImmutable::parse('2026-05-03 06:00:00'))->create();
    Page::factory()->published(CarbonImmutable::parse('2026-05-04 00:00:00'))->create();
    Page::factory()->published(CarbonImmutable::parse('2026-05-06 11:00:00'))->create();
    Page::factory()->published(CarbonImmutable::parse('2026-05-02 23:00:00'))->create();

    Page::factory()->pending()->create([
        'visible_from' => CarbonImmutable::parse('2026-05-06 18:00:00'),
    ]);
    Page::factory()->pending()->create([
        'visible_from' => CarbonImmutable::parse('2026-05-08 00:00:00'),
    ]);
    Page::factory()->pending()->create([
        'visible_from' => CarbonImmutable::parse('2026-05-10 01:00:00'),
    ]);

    $data = BuildPublishingTrendAction::run($rangeStart, $rangeEnd);

    expect(array_map(
        fn (PublishingTrendPointData $point): int => $point->publishedCount,
        $data->points,
    ))->toBe([1, 1, 0, 1, 0, 0, 0])
        ->and(array_map(
            fn (PublishingTrendPointData $point): int => $point->scheduledCount,
            $data->points,
        ))->toBe([0, 0, 0, 1, 0, 1, 0])
        ->and($data->totalPublished)->toBe(3)
        ->and($data->totalScheduled)->toBe(2);
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

    $pageSelectQueries = array_filter(
        DB::getQueryLog(),
        fn (array $entry): bool => str_starts_with(strtolower(ltrim((string) $entry['query'])), 'select')
            && str_contains(strtolower((string) $entry['query']), ' from "pages"'),
    );

    DB::disableQueryLog();

    // Two grouped bucket aggregates (published + scheduled), instead of the previous
    // 15 per-bucket COUNT round-trips and all-window scheduled total.
    expect(count($pageSelectQueries))->toBeLessThanOrEqual(2);
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

it('labels long-range publishing trend buckets as date ranges', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-06 12:00:00'));

    $data = BuildPublishingTrendAction::run(
        CarbonImmutable::parse('2026-01-01 00:00:00'),
        CarbonImmutable::parse('2027-01-01 00:00:00'),
    );

    expect($data->points[0]->label)->toBe('Jan 1 - Feb 22')
        ->and($data->points[6]->label)->toBe('Nov 9 - Dec 31');
});

it('does not expose unscoped publishing trend counts without an authenticated actor', function (): void {
    auth()->logout();

    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-03 12:00:00'));

    Page::factory()->published(CarbonImmutable::parse('2026-05-01 09:00:00'))->create();
    Page::factory()->pending()->create([
        'visible_from' => CarbonImmutable::parse('2026-05-03 18:00:00'),
    ]);

    $data = BuildPublishingTrendAction::run(...dashboardReportsThisWeekRange());

    expect($data->totalPublished)->toBe(0)
        ->and($data->totalScheduled)->toBe(0);
});
