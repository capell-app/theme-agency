<?php

declare(strict_types=1);

namespace Capell\UrlManager\Filament\Pages;

use BackedEnum;
use Capell\UrlManager\Enums\UrlManagerPermission;
use Capell\UrlManager\Filament\Pages\Tables\NotFoundOpportunitiesTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Override;

final class NotFoundOpportunitiesPage extends Page implements HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?int $navigationSort = 31;

    protected string $view = 'capell-admin::components.pages.table';

    protected static ?string $slug = 'url-manager/not-found-opportunities';

    public static function table(Table $table): Table
    {
        return NotFoundOpportunitiesTable::configure($table);
    }

    #[Override]
    public static function canAccess(): bool
    {
        return self::canViewNotFoundOpportunities();
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-url-manager::generic.not_found_opportunities');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return (string) __('capell-url-manager::generic.navigation_group');
    }

    public static function canManageNotFoundOpportunities(): bool
    {
        if (Gate::allows(UrlManagerPermission::ManageNotFoundOpportunities->value)) {
            return true;
        }

        return auth()->user()?->can(UrlManagerPermission::ManageNotFoundOpportunities->value) === true;
    }

    #[Override]
    public function getTitle(): string
    {
        return __('capell-url-manager::generic.not_found_opportunities');
    }

    #[Override]
    public function getSubheading(): string
    {
        return __('capell-url-manager::generic.not_found_opportunities_description');
    }

    private static function canViewNotFoundOpportunities(): bool
    {
        if (Gate::allows(UrlManagerPermission::ViewNotFoundOpportunitiesPage->value)) {
            return true;
        }

        if (auth()->user()?->can(UrlManagerPermission::ViewNotFoundOpportunitiesPage->value) === true) {
            return true;
        }

        return self::canManageNotFoundOpportunities();
    }
}
