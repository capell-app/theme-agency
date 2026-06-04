<?php

declare(strict_types=1);

use Capell\Admin\Contracts\Dashboard\ContentHealthDataProvider;
use Capell\Admin\Contracts\DashboardSettingsContributor;
use Capell\Admin\Enums\DashboardEnum;
use Capell\Admin\Support\CapellAdminManager;
use Capell\Admin\Support\Dashboard\NullContentHealthDataProvider;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\DashboardReports\Filament\Settings\Contributors\DashboardReportsDashboardSettingsContributor;
use Capell\DashboardReports\Filament\Widgets\ContentHealthWidget;
use Capell\DashboardReports\Filament\Widgets\PublishingTrendChartWidget;
use Capell\DashboardReports\Health\DashboardReportsHealthCheck;
use Capell\DashboardReports\Providers\DashboardReportsServiceProvider;
use Capell\DashboardReports\Tests\DashboardReportsTestCase;
use Illuminate\Container\Container;

uses(DashboardReportsTestCase::class);

/**
 * @param  list<class-string>  $widgets
 */
function dashboardReportsHealthSetMainDashboardWidgets(array $widgets): void
{
    $manager = app(CapellAdminManager::class);
    $property = new ReflectionProperty($manager, 'dashboardWidgets');
    $property->setAccessible(true);

    $property->setValue($manager, [
        DashboardEnum::Main->value => $widgets,
    ]);
}

function dashboardReportsHealthForgetTag(string $tag): void
{
    $property = new ReflectionProperty(Container::class, 'tags');
    $property->setAccessible(true);

    $tags = $property->getValue(app());
    $tags = is_array($tags) ? $tags : [];
    unset($tags[$tag]);

    $property->setValue(app(), $tags);
}

it('runs real diagnostics for dashboard reports installation health', function (): void {
    $results = DashboardReportsHealthCheck::runDiagnostics();
    $check = new DashboardReportsHealthCheck;

    expect($results)->toHaveCount(5)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue()
        ->and(DashboardReportsHealthCheck::passed())->toBeTrue()
        ->and($check->missingDashboardWidgets())->toBe([])
        ->and($check->missingDashboardSettingsKeys())->toBe([])
        ->and($results->first()?->label)->toBe(__('capell-dashboard-reports::dashboard.health_package_installed_label'))
        ->and($results->first()?->message)->toBe(__('capell-dashboard-reports::dashboard.health_package_installed_passed'));
});

it('fails when only the admin null content health provider is bound', function (): void {
    app()->instance(ContentHealthDataProvider::class, new NullContentHealthDataProvider);

    $check = new DashboardReportsHealthCheck;
    $result = $check->contentHealthProviderCheck();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe(__('capell-dashboard-reports::dashboard.health_content_health_provider_failed'))
        ->and($result->remediation)->toBe(__('capell-dashboard-reports::dashboard.health_content_health_provider_remediation'))
        ->and(DashboardReportsHealthCheck::passed())->toBeFalse();
});

it('fails when the content health provider binding cannot be resolved', function (): void {
    app()->bind(ContentHealthDataProvider::class, static function (): ContentHealthDataProvider {
        throw new RuntimeException('Dashboard Reports content health provider failed to resolve.');
    });

    $check = new DashboardReportsHealthCheck;
    $result = $check->contentHealthProviderCheck();

    expect($result->passed)->toBeFalse()
        ->and(DashboardReportsHealthCheck::passed())->toBeFalse();
});

it('fails when dashboard report widgets are missing from the main dashboard registry', function (): void {
    dashboardReportsHealthSetMainDashboardWidgets([
        PublishingTrendChartWidget::class,
    ]);

    $check = new DashboardReportsHealthCheck;
    $result = $check->dashboardWidgetsCheck();

    expect($check->missingDashboardWidgets())->toBe([ContentHealthWidget::class])
        ->and($result->passed)->toBeFalse()
        ->and($result->message)->toContain(ContentHealthWidget::class)
        ->and($result->remediation)->toBe(__('capell-dashboard-reports::dashboard.health_dashboard_widgets_remediation'))
        ->and(DashboardReportsHealthCheck::passed())->toBeFalse();
});

it('fails when dashboard reports settings are not tagged', function (): void {
    dashboardReportsHealthForgetTag(DashboardSettingsContributor::TAG);

    $check = new DashboardReportsHealthCheck;
    $result = $check->dashboardSettingsContributorCheck();

    expect($check->missingDashboardSettingsKeys())->toBe([
        'publishing_trend',
        'content_health',
    ])
        ->and($result->passed)->toBeFalse()
        ->and($result->message)->toContain('publishing_trend')
        ->and($result->message)->toContain('content_health')
        ->and($result->remediation)->toBe(__('capell-dashboard-reports::dashboard.health_dashboard_settings_contributor_remediation'))
        ->and(DashboardReportsHealthCheck::passed())->toBeFalse();
});

it('fails when the dashboard reports settings contributor binding is broken', function (): void {
    app()->bind(DashboardReportsDashboardSettingsContributor::class, static fn (): object => new stdClass);

    $check = new DashboardReportsHealthCheck;
    $result = $check->dashboardSettingsContributorCheck();

    expect($result->passed)->toBeFalse()
        ->and($check->missingDashboardSettingsKeys())->toBe([
            'publishing_trend',
            'content_health',
        ]);
});

it('fails when the package is not marked installed', function (): void {
    CapellCore::forcePackageInstalled(DashboardReportsServiceProvider::$packageName, false);

    $check = new DashboardReportsHealthCheck;
    $result = $check->packageInstalledCheck();

    expect($result->passed)->toBeFalse()
        ->and(DashboardReportsHealthCheck::passed())->toBeFalse();
});
