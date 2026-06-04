<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\DashboardReports\Actions\Dashboard\BuildDefaultContentHealthAction;
use Capell\DashboardReports\Support\Dashboard\DashboardReportsContentHealthDataProvider;
use Capell\DashboardReports\Tests\DashboardReportsTestCase;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

uses(DashboardReportsTestCase::class, CreatesAdminUser::class);

beforeEach(function (): void {
    Role::findOrCreate(config('capell.roles.super_admin', 'super_admin'));
});

it('builds default CMS content health issues from core pages', function (): void {
    $this->actingAsAdmin();

    Page::factory()->pending()->create();
    Page::factory()->expired()->create();

    $data = BuildDefaultContentHealthAction::run();
    $issues = capell_test_collect($data->issues->toArray())->keyBy('id');

    expect($issues->get('scheduled_pages')['count'])->toBe(1)
        ->and($issues->get('expired_pages')['count'])->toBe(1)
        ->and($issues->get('pages_without_urls')['count'])->toBe(2)
        ->and($issues->get('scheduled_pages')['label'])->toBe(__('capell-dashboard-reports::dashboard.issue_scheduled_pages'));
});

it('builds filtered page resource deep links for each content health issue', function (): void {
    $this->actingAsAdmin();

    Route::get('/admin/pages', fn (): string => '')->name('filament.admin.resources.pages.index');

    Page::factory()->pending()->create();

    $data = BuildDefaultContentHealthAction::run();
    $issues = capell_test_collect($data->issues->toArray())->keyBy('id');

    expect(str_contains((string) $issues->get('scheduled_pages')['filterUrl'], 'tableFilters%5Bdashboard_reports_health%5D%5Bvalue%5D=scheduled_pages'))->toBeTrue()
        ->and(str_contains((string) $issues->get('expired_pages')['filterUrl'], 'tableFilters%5Bdashboard_reports_health%5D%5Bvalue%5D=expired_pages'))->toBeTrue()
        ->and(str_contains((string) $issues->get('pages_without_urls')['filterUrl'], 'tableFilters%5Bdashboard_reports_health%5D%5Bvalue%5D=pages_without_urls'))->toBeTrue()
        ->and(str_contains((string) $issues->get('stale_pages')['filterUrl'], 'tableFilters%5Bdashboard_reports_health%5D%5Bvalue%5D=stale_pages'))->toBeTrue();
});

it('uses the configured stale page threshold through the content health provider', function (): void {
    $this->actingAsAdmin();

    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-03 12:00:00'));
    config()->set('capell-dashboard-reports.stale_page_threshold_days', 30);

    Page::factory()->published(CarbonImmutable::parse('2026-04-01 09:00:00'))->create([
        'updated_at' => CarbonImmutable::parse('2026-04-02 09:00:00'),
    ]);
    Page::factory()->published(CarbonImmutable::parse('2026-04-20 09:00:00'))->create([
        'updated_at' => CarbonImmutable::parse('2026-04-20 09:00:00'),
    ]);

    $data = (new DashboardReportsContentHealthDataProvider)->build();
    $issues = capell_test_collect($data->issues->toArray())->keyBy('id');

    expect($issues->get('stale_pages')['count'])->toBe(1)
        ->and($issues->get('stale_pages')['label'])->toBe(__('capell-dashboard-reports::dashboard.issue_stale_pages', ['days' => 30]));
});

it('does not expose unscoped content health counts without an authenticated actor', function (): void {
    auth()->logout();

    Page::factory()->pending()->create();
    Page::factory()->expired()->create();

    $data = BuildDefaultContentHealthAction::run();
    $issues = capell_test_collect($data->issues->toArray())->keyBy('id');

    expect($issues->get('scheduled_pages')['count'])->toBe(0)
        ->and($issues->get('expired_pages')['count'])->toBe(0)
        ->and($issues->get('pages_without_urls')['count'])->toBe(0)
        ->and($issues->get('stale_pages')['count'])->toBe(0);
});
