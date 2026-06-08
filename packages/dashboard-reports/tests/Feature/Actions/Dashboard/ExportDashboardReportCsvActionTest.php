<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\DashboardReports\Actions\Dashboard\ExportContentHealthCsvAction;
use Capell\DashboardReports\Actions\Dashboard\ExportPublishingTrendCsvAction;
use Capell\DashboardReports\Tests\DashboardReportsTestCase;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Carbon\CarbonImmutable;
use Spatie\Permission\Models\Role;

uses(DashboardReportsTestCase::class, CreatesAdminUser::class);

beforeEach(function (): void {
    Role::findOrCreate(dashboardReportsCsvSuperAdminRole());
    $this->actingAsAdmin();
});

it('exports content health widget rows as CSV', function (): void {
    Page::factory()->pending()->create();
    Page::factory()->expired()->create();

    $rows = dashboardReportsCsvRows(ExportContentHealthCsvAction::run());

    expect($rows[0])->toBe(['Issue ID', 'Label', 'Count', 'Filter URL'])
        ->and($rows[1][0])->toBe('scheduled_pages')
        ->and($rows[1][2])->toBe('1')
        ->and($rows[2][0])->toBe('expired_pages')
        ->and($rows[2][2])->toBe('1')
        ->and($rows[3][0])->toBe('pages_without_urls')
        ->and($rows[3][2])->toBe('2');
});

it('exports publishing trend widget buckets and totals as CSV', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-06 12:00:00'));

    $rangeStart = CarbonImmutable::parse('2026-05-03 00:00:00');
    $rangeEnd = CarbonImmutable::parse('2026-05-10 00:00:00');

    Page::factory()->published(CarbonImmutable::parse('2026-05-03 06:00:00'))->create();
    Page::factory()->pending()->create([
        'visible_from' => CarbonImmutable::parse('2026-05-06 18:00:00'),
    ]);

    $rows = dashboardReportsCsvRows(ExportPublishingTrendCsvAction::run($rangeStart, $rangeEnd));

    expect($rows[0])->toBe(['Bucket', 'Published pages', 'Scheduled pages'])
        ->and($rows)->toHaveCount(9)
        ->and($rows[1])->toBe(['May 3', '1', '0'])
        ->and($rows[8])->toBe(['Total', '1', '1']);
});

/**
 * @return list<list<string>>
 */
function dashboardReportsCsvRows(string $csv): array
{
    $lines = array_values(array_filter(explode("\n", trim($csv)), static fn (string $line): bool => $line !== ''));

    return array_map(
        static fn (string $line): array => array_map(
            static fn (?string $value): string => $value ?? '',
            str_getcsv($line),
        ),
        $lines,
    );
}

function dashboardReportsCsvSuperAdminRole(): string
{
    $role = config('capell.roles.super_admin', 'super_admin');

    return is_string($role) && $role !== '' ? $role : 'super_admin';
}
