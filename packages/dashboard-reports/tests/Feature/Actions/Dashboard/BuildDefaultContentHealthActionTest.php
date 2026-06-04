<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\DashboardReports\Actions\Dashboard\BuildDefaultContentHealthAction;
use Capell\DashboardReports\Tests\DashboardReportsTestCase;
use Illuminate\Support\Facades\Route;

uses(DashboardReportsTestCase::class);

it('builds default CMS content health issues from core pages', function (): void {
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
    Route::get('/admin/pages', fn (): string => '')->name('filament.admin.resources.pages.index');

    Page::factory()->pending()->create();

    $data = BuildDefaultContentHealthAction::run();
    $issues = capell_test_collect($data->issues->toArray())->keyBy('id');

    expect(str_contains((string) $issues->get('scheduled_pages')['filterUrl'], 'tableFilters%5Bdashboard_reports_health%5D%5Bvalue%5D=scheduled_pages'))->toBeTrue()
        ->and(str_contains((string) $issues->get('expired_pages')['filterUrl'], 'tableFilters%5Bdashboard_reports_health%5D%5Bvalue%5D=expired_pages'))->toBeTrue()
        ->and(str_contains((string) $issues->get('pages_without_urls')['filterUrl'], 'tableFilters%5Bdashboard_reports_health%5D%5Bvalue%5D=pages_without_urls'))->toBeTrue()
        ->and(str_contains((string) $issues->get('stale_pages')['filterUrl'], 'tableFilters%5Bdashboard_reports_health%5D%5Bvalue%5D=stale_pages'))->toBeTrue();
});
