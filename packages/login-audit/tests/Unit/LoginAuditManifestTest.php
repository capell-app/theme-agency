<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;
use Capell\Core\Contracts\Extensions\RegistersExtensionPermission;
use Capell\Core\Contracts\Extensions\RegistersExtensionSetting;
use Capell\Core\Contracts\Extensions\RegistersExtensionWidget;
use Capell\Core\Contracts\Extensions\RunsScheduledExtensionJob;
use Capell\LoginAudit\Actions\BuildLoginAuditsQueryAction;
use Capell\LoginAudit\Filament\Resources\LoginAudits\LoginAuditResource;
use Capell\LoginAudit\Filament\Widgets\LoginAuditsWidget;
use Capell\LoginAudit\Health\LoginAuditHealthCheck;
use Capell\LoginAudit\Manifest\LoginAuditAdminResourcesContribution;
use Capell\LoginAudit\Manifest\LoginAuditDashboardWidgetContribution;
use Capell\LoginAudit\Manifest\LoginAuditHealthContribution;
use Capell\LoginAudit\Manifest\LoginAuditModelsContribution;
use Capell\LoginAudit\Manifest\LoginAuditPermissionsContribution;
use Capell\LoginAudit\Manifest\LoginAuditPurgeScheduleContribution;
use Capell\LoginAudit\Manifest\LoginAuditSettingsContribution;
use Capell\LoginAudit\Models\LoginAudit;
use Capell\LoginAudit\Settings\LoginAuditSettings;

/**
 * @return array<string, mixed>
 */
function login_audit_manifest(): array
{
    $decoded = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($decoded), RuntimeException::class, 'Login Audit manifest must decode to an object.');

    return collect($decoded)->all();
}

it('declares the login audit extension surfaces in the manifest', function (): void {
    $manifest = login_audit_manifest();

    expect($manifest)
        ->toHaveKey('name', 'capell-app/login-audit')
        ->and(data_get($manifest, 'database.requiredTables', []))->toBe(['login_audit'])
        ->and($manifest['settings'] ?? [])->toContain(LoginAuditSettings::class)
        ->and($manifest['permissions'] ?? [])->toContain('View:LoginAudit')
        ->and(data_get($manifest, 'security.adminSurface.permissions', []))->toContain('View:LoginAudit')
        ->and(data_get($manifest, 'contributionTraceability.deferredContributions'))->toBe([])
        ->and(data_get($manifest, 'actions.buildLoginAuditsQuery'))->toBe(BuildLoginAuditsQueryAction::class);

    expect($manifest['contributes'] ?? [])->toContain(
        [
            'type' => 'admin-resource',
            'class' => LoginAuditAdminResourcesContribution::class,
            'resourceClass' => LoginAuditResource::class,
        ],
        [
            'type' => 'dashboard-widget',
            'class' => LoginAuditDashboardWidgetContribution::class,
            'widgetClass' => LoginAuditsWidget::class,
            'dashboard' => 'system-health',
        ],
        [
            'type' => 'model',
            'class' => LoginAuditModelsContribution::class,
            'modelClass' => LoginAudit::class,
        ],
        [
            'type' => 'setting',
            'class' => LoginAuditSettingsContribution::class,
            'settingsClass' => LoginAuditSettings::class,
            'settingsGroup' => 'login_audit',
        ],
        [
            'type' => 'permission',
            'class' => LoginAuditPermissionsContribution::class,
            'permissions' => ['View:LoginAudit'],
        ],
        [
            'type' => 'scheduled-job',
            'class' => LoginAuditPurgeScheduleContribution::class,
            'command' => 'authentication-log:purge',
            'frequency' => 'daily',
        ],
        [
            'type' => 'health-check',
            'class' => LoginAuditHealthContribution::class,
            'checkClass' => LoginAuditHealthCheck::class,
        ],
    );
});

it('keeps login audit manifest contribution classes on core extension contracts', function (): void {
    expect(class_implements(LoginAuditAdminResourcesContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(LoginAuditDashboardWidgetContribution::class))->toContain(RegistersExtensionWidget::class)
        ->and(class_implements(LoginAuditSettingsContribution::class))->toContain(RegistersExtensionSetting::class)
        ->and(class_implements(LoginAuditPermissionsContribution::class))->toContain(RegistersExtensionPermission::class)
        ->and(class_implements(LoginAuditPurgeScheduleContribution::class))->toContain(RunsScheduledExtensionJob::class)
        ->and(class_implements(LoginAuditHealthContribution::class))->toContain(ChecksExtensionHealth::class)
        ->and(LoginAuditHealthContribution::compatibleCapellApiVersion())->toBe('^4.0');
});
