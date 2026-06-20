<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionFilamentWidget;
use Capell\Diagnostics\Actions\Dashboard\BuildPackagesInstalledAction;
use Capell\Diagnostics\Actions\DashboardReports\BuildInfrastructureStatusAction;
use Capell\Diagnostics\Actions\DashboardReports\BuildPublicOutputSafetyReportAction;
use Capell\Diagnostics\Actions\Health\RunExtensionHealthChecksAction;
use Capell\Diagnostics\Actions\InspectPackageOwnershipAction;
use Capell\Diagnostics\Filament\Pages\CommandPalettePage;
use Capell\Diagnostics\Filament\Pages\DiagnosticsPage;
use Capell\Diagnostics\Filament\Pages\PermissionAuditPage;
use Capell\Diagnostics\Filament\Pages\QueueHealthPage;
use Capell\Diagnostics\Filament\Pages\SystemHealthPage;
use Capell\Diagnostics\Filament\Widgets\Health\SiteHealthFilamentWidget;
use Capell\Diagnostics\Manifest\CommandPalettePageContribution;
use Capell\Diagnostics\Manifest\DiagnosticsPageContribution;
use Capell\Diagnostics\Manifest\PermissionAuditPageContribution;
use Capell\Diagnostics\Manifest\QueueHealthPageContribution;
use Capell\Diagnostics\Manifest\SiteHealthWidgetContribution;
use Capell\Diagnostics\Manifest\SystemHealthPageContribution;
use Capell\Diagnostics\Manifest\SystemHealthWidgetsContribution;
use Capell\Diagnostics\Providers\AdminServiceProvider;
use Capell\Diagnostics\Providers\DiagnosticsServiceProvider;
use Illuminate\Support\Facades\File;

describe('diagnostics capell.json manifest', function (): void {
    it('declares admin and console package metadata', function (): void {
        $manifest = json_decode(
            File::get(__DIR__ . '/../../capell.json'),
            associative: true,
        );

        expect($manifest)
            ->toMatchArray([
                'name' => 'capell-app/diagnostics',
                'kind' => 'package',
                'capellApiVersion' => '^4.0',
            ])
            ->and($manifest['surfaces'])->toContain('admin')
            ->and($manifest['providers']['runtime'])->toContain(DiagnosticsServiceProvider::class)
            ->and($manifest['providers']['admin'])->toContain(AdminServiceProvider::class)
            ->and($manifest['database']['requiredTables'])->toContain(
                'command_palette_runs',
                'queue_monitors',
            );
    });

    it('declares implemented diagnostics pages widgets and gap coverage actions', function (): void {
        $manifest = json_decode(
            File::get(__DIR__ . '/../../capell.json'),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        expect($manifest['description'])->toContain('per-package install health')
            ->and($manifest['marketplace']['summary'])->toContain('per-package install health')
            ->and($manifest['contributes'])->toContain([
                'type' => 'admin-page',
                'class' => DiagnosticsPageContribution::class,
                'pageClass' => DiagnosticsPage::class,
                'labelKey' => 'capell-diagnostics::package.diagnostics',
            ])
            ->and($manifest['contributes'])->toContain([
                'type' => 'admin-page',
                'class' => CommandPalettePageContribution::class,
                'pageClass' => CommandPalettePage::class,
                'labelKey' => 'capell-diagnostics::package.command_palette',
            ])
            ->and($manifest['contributes'])->toContain([
                'type' => 'admin-page',
                'class' => SystemHealthPageContribution::class,
                'pageClass' => SystemHealthPage::class,
                'labelKey' => 'capell-diagnostics::package.system_health',
            ])
            ->and($manifest['contributes'])->toContain([
                'type' => 'admin-page',
                'class' => QueueHealthPageContribution::class,
                'pageClass' => QueueHealthPage::class,
                'labelKey' => 'capell-diagnostics::package.queue_health',
            ])
            ->and($manifest['contributes'])->toContain([
                'type' => 'admin-page',
                'class' => PermissionAuditPageContribution::class,
                'pageClass' => PermissionAuditPage::class,
                'labelKey' => 'capell-diagnostics::package.permission_audit',
            ])
            ->and($manifest['contributes'])->toContain([
                'type' => 'dashboard-widget',
                'class' => SiteHealthWidgetContribution::class,
                'widgetClass' => SiteHealthFilamentWidget::class,
            ])
            ->and(class_implements(SiteHealthWidgetContribution::class))->toContain(RegistersExtensionFilamentWidget::class)
            ->and(class_implements(SystemHealthWidgetsContribution::class))->toContain(RegistersExtensionFilamentWidget::class)
            ->and($manifest['actions'])->toHaveKey('buildPackagesInstalled', BuildPackagesInstalledAction::class)
            ->and($manifest['actions'])->toHaveKey('buildInfrastructureStatus', BuildInfrastructureStatusAction::class)
            ->and($manifest['actions'])->toHaveKey('buildPublicOutputSafetyReport', BuildPublicOutputSafetyReportAction::class)
            ->and($manifest['actions'])->toHaveKey('inspectPackageOwnership', InspectPackageOwnershipAction::class)
            ->and($manifest['actions'])->toHaveKey('runExtensionHealthChecks', RunExtensionHealthChecksAction::class)
            ->and($manifest['commands']['doctor'])->toBe('capell:diagnostics:health')
            ->and($manifest['capabilities'])->toContain(
                'diagnostics-cross-package-install-health',
                'diagnostics-public-output-safety',
                'diagnostics-infrastructure-status',
                'diagnostics-package-ownership',
            )
            ->and($manifest['contributionTraceability']['deferredContributions'])->not->toContain(
                'admin-page',
                'dashboard-widget',
                'page-type',
                'route',
            );
    });
});
