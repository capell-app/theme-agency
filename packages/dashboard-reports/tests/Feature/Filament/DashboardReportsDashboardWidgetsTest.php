<?php

declare(strict_types=1);

use Capell\Admin\Contracts\Dashboard\ContentHealthDataProvider;
use Capell\Admin\Data\Dashboard\ContentHealthData;
use Capell\Admin\Data\Dashboard\ContentHealthIssueData;
use Capell\Admin\Enums\DashboardEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Models\Page;
use Capell\DashboardReports\Filament\Widgets\ContentHealthWidget;
use Capell\DashboardReports\Filament\Widgets\PublishingTrendChartWidget;
use Capell\DashboardReports\Providers\AdminServiceProvider;
use Capell\DashboardReports\Support\Dashboard\DashboardReportsContentHealthDataProvider;
use Capell\DashboardReports\Support\Dashboard\DashboardReportsSettingsResolver;
use Capell\DashboardReports\Tests\DashboardReportsTestCase;
use Capell\Tests\Support\Concerns\CreatesAdminUser;

use function Pest\Livewire\livewire;

use Spatie\LaravelData\DataCollection;
use Spatie\Permission\Models\Role;

uses(DashboardReportsTestCase::class, CreatesAdminUser::class);

beforeEach(function (): void {
    Role::findOrCreate(config('capell.roles.editor', 'editor'));
});

it('registers dashboard-dashboard_reports dashboard widgets on the main dashboard', function (): void {
    expect(CapellAdmin::getDashboardWidgets(DashboardEnum::Main))
        ->toContain(PublishingTrendChartWidget::class)
        ->toContain(ContentHealthWidget::class);
});

it('binds dashboard-dashboard_reports content health as the installed content health provider', function (): void {
    expect(resolve(ContentHealthDataProvider::class))
        ->toBeInstanceOf(DashboardReportsContentHealthDataProvider::class);
});

it('resolves the configured stale page threshold for content health', function (): void {
    config()->set('capell-dashboard-reports.stale_page_threshold_days', 30);

    expect(resolve(DashboardReportsSettingsResolver::class)->settings()->stalePageThresholdDays)->toBe(30);
});

it('falls back to the default stale page threshold when configuration is invalid', function (mixed $configured, int $expected): void {
    config()->set('capell-dashboard-reports.stale_page_threshold_days', $configured);

    expect(resolve(DashboardReportsSettingsResolver::class)->settings()->stalePageThresholdDays)->toBe($expected);
})->with([
    'missing' => [null, 90],
    'not numeric' => ['soon', 90],
    'too small' => [0, 1],
    'too large' => [4000, 3650],
]);

it('does not replace another package content health provider', function (): void {
    $externalContentHealthDataProvider = new class implements ContentHealthDataProvider
    {
        public function build(): ContentHealthData
        {
            return new ContentHealthData(
                issues: ContentHealthIssueData::collect([], DataCollection::class),
            );
        }
    };

    app()->instance(ContentHealthDataProvider::class, $externalContentHealthDataProvider);

    $method = new ReflectionMethod(AdminServiceProvider::class, 'registerDashboardDataProviders');
    $method->invoke(new AdminServiceProvider(app()));

    expect(resolve(ContentHealthDataProvider::class))->toBe($externalContentHealthDataProvider);
});

it('uses dashboard-dashboard_reports-owned translations and views for dashboard-dashboard_reports widgets', function (): void {
    $contentHealthWidget = new ContentHealthWidget;
    $contentHealthView = (fn (): string => $this->view)->call($contentHealthWidget);

    expect((new PublishingTrendChartWidget)->getHeading())->toBe(__('capell-dashboard-reports::dashboard.widget_publishing_trend'))
        ->and($contentHealthView)->toBe('capell-dashboard-reports::widgets.content-health');
});

it('builds content health data once per request across canView and data', function (): void {
    $this->actingAsRole(config('capell.roles.editor', 'editor'));

    $countingContentHealthDataProvider = new class implements ContentHealthDataProvider
    {
        public int $buildCount = 0;

        public function build(): ContentHealthData
        {
            $this->buildCount++;

            return new ContentHealthData(
                issues: ContentHealthIssueData::collect([
                    new ContentHealthIssueData(
                        id: 'scheduled_pages',
                        label: 'Scheduled pages',
                        count: 1,
                        filterUrl: null,
                    ),
                ], DataCollection::class),
            );
        }
    };

    app()->instance(ContentHealthDataProvider::class, $countingContentHealthDataProvider);

    expect(ContentHealthWidget::canView())->toBeTrue()
        ->and((new ContentHealthWidget)->data()->issues->count())->toBe(1)
        ->and($countingContentHealthDataProvider->buildCount)->toBe(1);
});

it('forgets content health data between request scopes', function (): void {
    $countingContentHealthDataProvider = new class implements ContentHealthDataProvider
    {
        public int $buildCount = 0;

        public function build(): ContentHealthData
        {
            $this->buildCount++;

            return new ContentHealthData(
                issues: ContentHealthIssueData::collect([
                    new ContentHealthIssueData(
                        id: 'scheduled_pages',
                        label: 'Scheduled pages',
                        count: $this->buildCount,
                        filterUrl: null,
                    ),
                ], DataCollection::class),
            );
        }
    };

    app()->instance(ContentHealthDataProvider::class, $countingContentHealthDataProvider);

    $firstRequestData = (new ContentHealthWidget)->data();

    app()->forgetScopedInstances();

    $secondRequestData = (new ContentHealthWidget)->data();

    expect($countingContentHealthDataProvider->buildCount)->toBe(2)
        ->and(collect($firstRequestData->issues->items())->first()?->count)->toBe(1)
        ->and(collect($secondRequestData->issues->items())->first()?->count)->toBe(2);
});

it('builds content health data through the installed provider and widget data contract', function (): void {
    Page::factory()->pending()->create();

    $providerData = resolve(ContentHealthDataProvider::class)->build();

    $widgetContentHealthDataProvider = new class implements ContentHealthDataProvider
    {
        public function build(): ContentHealthData
        {
            return new ContentHealthData(
                issues: ContentHealthIssueData::collect([
                    new ContentHealthIssueData(
                        id: 'custom_issue',
                        label: 'Custom issue',
                        count: 2,
                        filterUrl: '/admin/pages',
                    ),
                ], DataCollection::class),
            );
        }
    };

    app()->instance(ContentHealthDataProvider::class, $widgetContentHealthDataProvider);
    $widgetData = (new ContentHealthWidget)->data();

    $providerIssues = collect($providerData->issues->toArray())->keyBy('id');
    $firstWidgetIssue = collect($widgetData->issues->items())->first();

    throw_unless($firstWidgetIssue instanceof ContentHealthIssueData, RuntimeException::class, 'Expected content health widget to return a first issue.');

    expect($providerIssues)->toHaveKey('scheduled_pages')
        ->and($firstWidgetIssue->id)->toBe('custom_issue')
        ->and($firstWidgetIssue->count)->toBe(2);
});

it('hides content health when the provider has no issues', function (): void {
    $this->actingAsRole(config('capell.roles.editor', 'editor'));

    $emptyContentHealthDataProvider = new class implements ContentHealthDataProvider
    {
        public int $buildCount = 0;

        public function build(): ContentHealthData
        {
            $this->buildCount++;

            return new ContentHealthData(
                issues: ContentHealthIssueData::collect([], DataCollection::class),
            );
        }
    };

    app()->instance(ContentHealthDataProvider::class, $emptyContentHealthDataProvider);

    expect(ContentHealthWidget::canView())->toBeFalse()
        ->and($emptyContentHealthDataProvider->buildCount)->toBe(1);
});

it('renders content health issue links for an authenticated editor', function (): void {
    $this->actingAsRole(config('capell.roles.editor', 'editor'));

    $contentHealthDataProvider = new class implements ContentHealthDataProvider
    {
        public int $buildCount = 0;

        public function build(): ContentHealthData
        {
            $this->buildCount++;

            return new ContentHealthData(
                issues: ContentHealthIssueData::collect([
                    new ContentHealthIssueData(
                        id: 'scheduled_pages',
                        label: 'Scheduled pages',
                        count: 3,
                        filterUrl: '/admin/pages?tableFilters%5Bdashboard_reports_health%5D%5Bvalue%5D=scheduled_pages',
                    ),
                ], DataCollection::class),
            );
        }
    };

    app()->instance(ContentHealthDataProvider::class, $contentHealthDataProvider);

    expect(ContentHealthWidget::canView())->toBeTrue();

    livewire(ContentHealthWidget::class)
        ->assertOk()
        ->assertSee('Scheduled pages')
        ->assertSeeHtml('tableFilters%5Bdashboard_reports_health%5D%5Bvalue%5D=scheduled_pages');

    expect($contentHealthDataProvider->buildCount)->toBe(1);
});
