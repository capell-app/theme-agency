<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Filament\Pages;

use BackedEnum;
use Capell\Diagnostics\Enums\DiagnosticsPermission;
use Capell\Diagnostics\Filament\Pages\Tables\PermissionAuditTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Override;

class PermissionAuditPage extends Page implements HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $slug = 'reports/permission-audit';

    protected static ?int $navigationSort = 3;

    protected string $view = 'capell-admin::components.pages.table';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-admin::navigation.permission_audit');
    }

    #[Override]
    public static function canAccess(): bool
    {
        return auth()->user()?->can(DiagnosticsPermission::ViewPermissionAuditPage->value) ?? false;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return (string) __('capell-admin::navigation.group_reports');
    }

    #[Override]
    public function getTitle(): string
    {
        return __('capell-diagnostics::package.permission_audit');
    }

    public function table(Table $table): Table
    {
        return PermissionAuditTable::configure($table);
    }
}
