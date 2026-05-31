<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Filament\Pages;

use BackedEnum;
use Capell\Admin\Contracts\RegistryInspectorInterface;
use Capell\Admin\Filament\Actions\Makers\RunMakerFilamentAction;
use Capell\Core\Contracts\Makers\Maker;
use Capell\Core\Contracts\Makers\MakerRegistryInterface;
use Capell\Core\Data\Makers\MakerDefinitionData;
use Capell\Core\Support\Makers\MakerSafety;
use Capell\Diagnostics\Enums\DiagnosticsPermission;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Override;

class DiagnosticsPage extends Page implements HasActions
{
    use InteractsWithActions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCommandLine;

    protected static ?string $slug = 'diagnostics';

    protected static ?int $navigationSort = 9;

    protected string $view = 'capell-diagnostics::filament.pages.diagnostics';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-diagnostics::package.diagnostics');
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-admin::navigation.group_system');
    }

    #[Override]
    public static function canAccess(): bool
    {
        return config('capell.dashboard.developer_page_enabled', true) === true
            && (
                self::userHasSuperAdminRole()
                || Gate::allows(DiagnosticsPermission::AccessDiagnostics->value)
                || Gate::allows(DiagnosticsPermission::ViewDiagnostics->value)
                || auth()->user()?->can(DiagnosticsPermission::AccessDiagnostics->value) === true
            );
    }

    public static function userCanRunMakers(): bool
    {
        $user = auth()->user();
        if (self::userHasSuperAdminRole()) {
            return true;
        }

        if (Gate::allows(DiagnosticsPermission::AccessDiagnostics->value)) {
            return true;
        }

        return $user?->can(DiagnosticsPermission::AccessDiagnostics->value) === true;
    }

    #[Override]
    public function getTitle(): string|Htmlable
    {
        return __('capell-diagnostics::package.diagnostics');
    }

    /**
     * @return Collection<array-key, mixed>
     */
    public function makers(): Collection
    {
        return resolve(MakerRegistryInterface::class)->all()
            ->map(fn (Maker $maker): MakerDefinitionData => $maker->definition());
    }

    /**
     * @return array<array-key, mixed>
     */
    public function safety(): array
    {
        return resolve(MakerSafety::class)->current()->toArray();
    }

    /**
     * @return Collection<array-key, mixed>
     */
    public function configurators(): Collection
    {
        return resolve(RegistryInspectorInterface::class)->configurators();
    }

    /**
     * @return Collection<array-key, mixed>
     */
    public function components(): Collection
    {
        return resolve(RegistryInspectorInterface::class)->components();
    }

    /**
     * @return Collection<array-key, mixed>
     */
    public function blocks(): Collection
    {
        return resolve(RegistryInspectorInterface::class)->blocks();
    }

    /**
     * @return array<int, Action>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return $this->makers()
            ->map(
                fn (MakerDefinitionData $maker): Action => RunMakerFilamentAction::make($maker->key)
                    ->authorize(fn (): bool => self::userCanRunMakers())
                    ->hidden(fn (): bool => ! self::userCanRunMakers()),
            )
            ->values()
            ->all();
    }

    private static function userHasSuperAdminRole(): bool
    {
        $user = auth()->user();

        if ($user === null || ! method_exists($user, 'hasRole')) {
            return false;
        }

        $superAdminRole = config('capell.roles.super_admin', 'super_admin');

        return is_string($superAdminRole) && $superAdminRole !== '' && $user->hasRole($superAdminRole);
    }
}
