<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Providers;

use Capell\Admin\Enums\DashboardEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Database\RuntimeSchemaState;
use Capell\Diagnostics\Actions\EnsureDiagnosticsPermissionsAction;
use Capell\Diagnostics\Filament\Pages\CommandPalettePage;
use Capell\Diagnostics\Filament\Pages\DiagnosticsPage;
use Capell\Diagnostics\Filament\Pages\PermissionAuditPage;
use Capell\Diagnostics\Filament\Pages\QueueHealthPage;
use Capell\Diagnostics\Filament\Pages\SystemHealthPage;
use Capell\Diagnostics\Filament\Widgets\Health\AlertsFilamentWidget;
use Capell\Diagnostics\Filament\Widgets\Health\CacheHealthFilamentWidget;
use Capell\Diagnostics\Filament\Widgets\Health\ConfigDriftFilamentWidget;
use Capell\Diagnostics\Filament\Widgets\Health\ContentGraphHealthFilamentWidget;
use Capell\Diagnostics\Filament\Widgets\Health\ContentHealthFilamentWidget;
use Capell\Diagnostics\Filament\Widgets\Health\MigrationsHealthFilamentWidget;
use Capell\Diagnostics\Filament\Widgets\Health\PackagesInstalledFilamentWidget;
use Capell\Diagnostics\Filament\Widgets\Health\RegistryHealthFilamentWidget;
use Capell\Diagnostics\Filament\Widgets\Health\SetupHealthFilamentWidget;
use Capell\Diagnostics\Filament\Widgets\Health\SiteHealthFilamentWidget;
use Capell\Diagnostics\Filament\Widgets\Health\TailwindBuildStatusFilamentWidget;
use Capell\Diagnostics\Palette\CapellArtisanPaletteCommandProvider;
use Capell\Diagnostics\Palette\DiagnosticsPaletteCommandProvider;
use Illuminate\Support\ServiceProvider;
use Override;

final class AdminServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->tag([
            CapellArtisanPaletteCommandProvider::class,
            DiagnosticsPaletteCommandProvider::class,
        ], 'capell.diagnostics.command-palette-provider');

        if (! $this->isPackageInstalled()) {
            return;
        }

        $this
            ->registerPages()
            ->registerDashboardFilamentWidgets();
    }

    public function boot(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this
            ->registerPages()
            ->registerDashboardFilamentWidgets()
            ->ensurePermissions();
    }

    private function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(DiagnosticsServiceProvider::$packageName);
    }

    private function registerPages(): self
    {
        if (! class_exists(CapellAdmin::class)) {
            return $this;
        }

        CapellAdmin::registerExtensionPage(
            DiagnosticsServiceProvider::$packageName,
            DiagnosticsPage::class,
        );
        CapellAdmin::registerExtensionPage(
            DiagnosticsServiceProvider::$packageName,
            CommandPalettePage::class,
        );
        CapellAdmin::registerExtensionPage(
            DiagnosticsServiceProvider::$packageName,
            SystemHealthPage::class,
        );
        CapellAdmin::registerExtensionPage(
            DiagnosticsServiceProvider::$packageName,
            QueueHealthPage::class,
        );
        CapellAdmin::registerExtensionPage(
            DiagnosticsServiceProvider::$packageName,
            PermissionAuditPage::class,
        );

        return $this;
    }

    private function ensurePermissions(): self
    {
        $table = config('permission.table_names.permissions', 'permissions');

        if (is_string($table) && resolve(RuntimeSchemaState::class)->hasTable($table)) {
            EnsureDiagnosticsPermissionsAction::run();
        }

        return $this;
    }

    private function registerDashboardFilamentWidgets(): self
    {
        if (! class_exists(CapellAdmin::class) || ! class_exists(DashboardEnum::class)) {
            return $this;
        }

        CapellAdmin::registerDashboardFilamentWidget(SiteHealthFilamentWidget::class, DashboardEnum::Main);

        CapellAdmin::registerDashboardFilamentWidget(SetupHealthFilamentWidget::class, DashboardEnum::SystemHealth);
        CapellAdmin::registerDashboardFilamentWidget(AlertsFilamentWidget::class, DashboardEnum::SystemHealth);
        CapellAdmin::registerDashboardFilamentWidget(ContentHealthFilamentWidget::class, DashboardEnum::SystemHealth);
        CapellAdmin::registerDashboardFilamentWidget(ContentGraphHealthFilamentWidget::class, DashboardEnum::SystemHealth);
        CapellAdmin::registerDashboardFilamentWidget(RegistryHealthFilamentWidget::class, DashboardEnum::SystemHealth);
        CapellAdmin::registerDashboardFilamentWidget(MigrationsHealthFilamentWidget::class, DashboardEnum::SystemHealth);
        CapellAdmin::registerDashboardFilamentWidget(PackagesInstalledFilamentWidget::class, DashboardEnum::SystemHealth);
        CapellAdmin::registerDashboardFilamentWidget(ConfigDriftFilamentWidget::class, DashboardEnum::SystemHealth);
        CapellAdmin::registerDashboardFilamentWidget(CacheHealthFilamentWidget::class, DashboardEnum::SystemHealth);
        CapellAdmin::registerDashboardFilamentWidget(TailwindBuildStatusFilamentWidget::class, DashboardEnum::SystemHealth);

        return $this;
    }
}
