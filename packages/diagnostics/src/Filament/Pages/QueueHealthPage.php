<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Filament\Pages;

use BackedEnum;
use BadMethodCallException;
use Capell\Diagnostics\Enums\DiagnosticsPermission;
use Capell\Diagnostics\Filament\Pages\Tables\QueueHealthTable;
use Capell\Diagnostics\Filament\Widgets\QueueOperationsStatsWidget;
use Capell\Diagnostics\Models\FailedJob;
use Capell\Diagnostics\Models\PendingQueueJob;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Resources\Concerns\HasTabs;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Override;

class QueueHealthPage extends Page implements HasActions, HasTable
{
    use HasTabs;
    use InteractsWithActions;
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static ?string $slug = 'dashboard-dashboard_reports/queue-health';

    protected static ?int $navigationSort = 2;

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-admin::navigation.queue_health');
    }

    #[Override]
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        try {
            $superAdminRole = config('capell.roles.super_admin', 'super_admin');

            if (is_string($superAdminRole) && $superAdminRole !== '' && $user->hasRole($superAdminRole)) {
                return true;
            }
        } catch (BadMethodCallException) {
            // Role system not available; fall back to diagnostics permissions.
        }

        if (Gate::allows(DiagnosticsPermission::AccessDiagnostics->value)) {
            return true;
        }

        if (Gate::allows(DiagnosticsPermission::ManageQueueHealthPage->value)) {
            return true;
        }

        if (Gate::allows(DiagnosticsPermission::ViewDiagnostics->value)) {
            return true;
        }

        if ($user->can(DiagnosticsPermission::AccessDiagnostics->value) === true) {
            return true;
        }

        if ($user->can(DiagnosticsPermission::ManageQueueHealthPage->value) === true) {
            return true;
        }

        return $user->can(DiagnosticsPermission::ViewDiagnostics->value) === true;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return (string) (__('capell-admin::navigation.group_system'));
    }

    #[Override]
    public function getTitle(): string
    {
        return __('capell-diagnostics::package.queue_health');
    }

    public function mount(): void
    {
        $this->loadDefaultActiveTab();
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $tabs = [
            'history' => Tab::make(__('capell-diagnostics::package.queue_operations_tab_history')),
        ];

        $failedJob = new FailedJob;

        if (SchemaFacade::connection($failedJob->getConnectionName())->hasTable($failedJob->getTable())) {
            $tabs['failed'] = Tab::make(__('capell-diagnostics::package.queue_operations_tab_failed'));
        }

        if ($this->shouldShowPendingJobsTab()) {
            $tabs['pending'] = Tab::make(__('capell-diagnostics::package.queue_operations_tab_pending'));
        }

        return $tabs;
    }

    #[Override]
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getTabsContentComponent(),
                EmbeddedTable::make(),
            ]);
    }

    public function table(Table $table): Table
    {
        return QueueHealthTable::configure($table, $this);
    }

    public function shouldShowPendingJobsTab(): bool
    {
        $pendingJob = new PendingQueueJob;

        return config('queue.default') === 'database'
            && (bool) config('capell-diagnostics.queue_monitor.pending_jobs_enabled', true)
            && SchemaFacade::connection($pendingJob->getConnectionName())->hasTable($pendingJob->getTable());
    }

    /**
     * @return array<int, class-string>
     */
    #[Override]
    protected function getHeaderWidgets(): array
    {
        return [
            QueueOperationsStatsWidget::class,
        ];
    }
}
