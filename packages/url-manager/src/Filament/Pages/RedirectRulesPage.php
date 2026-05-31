<?php

declare(strict_types=1);

namespace Capell\UrlManager\Filament\Pages;

use BackedEnum;
use Capell\UrlManager\Enums\UrlManagerPermission;
use Capell\UrlManager\Filament\Pages\Tables\RedirectRulesTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Override;

final class RedirectRulesPage extends Page implements HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static ?int $navigationSort = 30;

    protected string $view = 'capell-admin::components.pages.table';

    protected static ?string $slug = 'url-manager/redirects';

    public static function table(Table $table): Table
    {
        return RedirectRulesTable::configure($table);
    }

    #[Override]
    public static function canAccess(): bool
    {
        return self::canViewRedirectRules();
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-url-manager::generic.redirects');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return (string) __('capell-url-manager::generic.navigation_group');
    }

    public static function canManageRedirectRules(): bool
    {
        if (Gate::allows(UrlManagerPermission::ManageRedirectRules->value)) {
            return true;
        }

        return auth()->user()?->can(UrlManagerPermission::ManageRedirectRules->value) === true;
    }

    #[Override]
    public function getTitle(): string
    {
        return __('capell-url-manager::generic.redirects');
    }

    #[Override]
    public function getSubheading(): string
    {
        return __('capell-url-manager::generic.redirects_description');
    }

    private static function canViewRedirectRules(): bool
    {
        if (Gate::allows(UrlManagerPermission::ViewRedirectRulesPage->value)) {
            return true;
        }

        if (auth()->user()?->can(UrlManagerPermission::ViewRedirectRulesPage->value) === true) {
            return true;
        }

        return self::canManageRedirectRules();
    }
}
