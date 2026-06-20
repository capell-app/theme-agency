<?php

declare(strict_types=1);

use Capell\Admin\Data\Bridges\AdminBridgeContextData;
use Capell\LoginAudit\Bridges\LoginAuditAdminBridge;
use Capell\LoginAudit\Filament\Extenders\LoginAuditAdminPanelExtender;
use Capell\LoginAudit\Filament\Resources\LoginAudits\LoginAuditResource;
use Capell\LoginAudit\Filament\Settings\Contributors\LoginAuditDashboardSettingsContributor;
use Capell\LoginAudit\Filament\Widgets\LoginAuditsFilamentWidget;
use Capell\LoginAudit\Http\Middleware\AdminActivityMiddleware;
use Capell\LoginAudit\Models\LoginAudit;
use Filament\Panel;

it('declares login audit dashboard settings keys', function (): void {
    expect((new LoginAuditDashboardSettingsContributor)->settingsKeys())->toBe([
        [
            'key' => 'login_audits',
            'label' => __('capell-login-audit::dashboard.access_logs'),
            'group' => __('capell-login-audit::dashboard.group'),
        ],
    ])->and((new LoginAuditAdminBridge)->isEnabled(AdminBridgeContextData::forPackage('capell-login-audit')))->toBeTrue();
});

it('declares login audit resource labels and pages', function (): void {
    expect(LoginAuditResource::getModel())->toBe(LoginAudit::class)
        ->and(LoginAuditResource::getNavigationGroup())->toBe('capell-admin::navigation.group_users')
        ->and(LoginAuditResource::getNavigationLabel())->toBe('Access Logs')
        ->and(LoginAuditResource::getPluralModelLabel())->toBe('Authentication Logs')
        ->and(LoginAuditResource::getPages())->toHaveKeys(['index']);
});

it('builds login audit widget table metadata and missing-authenticatable row state', function (): void {
    $widget = (new ReflectionClass(LoginAuditsFilamentWidget::class))->newInstanceWithoutConstructor();

    expect(invokeLoginAuditsWidgetMethod($widget, 'getTableQuery')->getModel())->toBeInstanceOf(LoginAudit::class)
        ->and(invokeLoginAuditsWidgetMethod($widget, 'getTableColumns'))->toHaveCount(1)
        ->and(invokeLoginAuditsWidgetMethod($widget, 'getFilamentUrl', [new LoginAudit]))->toBe('');
});

it('registers authentication log plugin and admin activity middleware on panels', function (): void {
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('hasPlugin')->once()->with('authentication-log')->andReturn(false);
    $panel->shouldReceive('plugin')->once()->andReturnSelf();
    $panel->shouldReceive('middleware')->once()->with([AdminActivityMiddleware::class], true)->andReturnSelf();

    (new LoginAuditAdminPanelExtender)->extend($panel);

    $alreadyConfiguredPanel = Mockery::mock(Panel::class);
    $alreadyConfiguredPanel->shouldReceive('hasPlugin')->once()->with('authentication-log')->andReturn(true);
    $alreadyConfiguredPanel->shouldReceive('plugin')->never();
    $alreadyConfiguredPanel->shouldReceive('middleware')->once()->with([AdminActivityMiddleware::class], true)->andReturnSelf();

    (new LoginAuditAdminPanelExtender)->extend($alreadyConfiguredPanel);
});

/**
 * @param  list<mixed>  $parameters
 */
function invokeLoginAuditsWidgetMethod(LoginAuditsFilamentWidget $widget, string $methodName, array $parameters = []): mixed
{
    $reflectionMethod = new ReflectionMethod(LoginAuditsFilamentWidget::class, $methodName);

    return $reflectionMethod->invokeArgs($widget, $parameters);
}
