<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Filament\Widgets\Health;

use Capell\Admin\Data\MessageData;
use Capell\Admin\Enums\AlertTypeEnum;
use Capell\Admin\Filament\Resources\Blueprints\BlueprintResource;
use Capell\Admin\Filament\Resources\Languages\LanguageResource;
use Capell\Admin\Filament\Resources\Sites\SiteResource;
use Capell\Admin\Filament\Resources\Themes\ThemeResource;
use Capell\Admin\Filament\Widgets\ResourceAlertsFilamentWidget;
use Capell\Core\Enums\BlueprintSubjectEnum;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\Installer\Providers\InstallerServiceProvider;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Override;

final class AlertsFilamentWidget extends ResourceAlertsFilamentWidget
{
    protected static ?int $sort = -1;

    #[Override]
    public static function canView(): bool
    {
        if (! parent::canView()) {
            return false;
        }

        $hasAllTypes = Blueprint::query()->count() >= count(BlueprintSubjectEnum::cases());
        $hasFoundationTheme = Theme::query()->default()->exists();
        $hasDefaultLanguage = Language::query()->default()->exists();
        $hasSite = Site::query()->exists();

        $everythingConfigured = $hasAllTypes && $hasFoundationTheme && $hasDefaultLanguage && $hasSite;

        return ! $everythingConfigured || class_exists(InstallerServiceProvider::class);
    }

    public function createLanguageAction(): Action
    {
        return Action::make('createLanguage')
            ->label(__('capell-admin::button.create', ['name' => 'Language']))
            ->color('warning')
            ->outlined()
            ->size('sm')
            ->url(fn (): string => LanguageResource::getUrl(parameters: ['tableAction' => 'create']));
    }

    public function createThemeAction(): Action
    {
        return Action::make('createTheme')
            ->label(__('capell-admin::button.create', ['name' => 'Theme']))
            ->color('warning')
            ->outlined()
            ->size('sm')
            ->url(fn (): string => ThemeResource::getUrl(parameters: ['tableAction' => 'create']));
    }

    public function createSiteAction(): Action
    {
        return Action::make('createSite')
            ->label(__('capell-admin::button.create', ['name' => 'Site']))
            ->color('warning')
            ->outlined()
            ->size('sm')
            ->url(fn (): string => SiteResource::getUrl('create'));
    }

    public function createTypeAction(): Action
    {
        return Action::make('createType')
            ->label(__('capell-admin::button.create', ['name' => 'Type']))
            ->color('warning')
            ->outlined()
            ->size('sm')
            ->url(fn (): string => BlueprintResource::getUrl(parameters: ['tableAction' => 'create']));
    }

    public function viewInstallerAction(): Action
    {
        return Action::make('viewInstaller')
            ->label((string) __('capell-admin::button.open_installer'))
            ->color('info')
            ->outlined()
            ->size('sm')
            ->url(function (): string {
                if (! Route::has('capell-installer.show')) {
                    return '#';
                }

                return route('capell-installer.show');
            });
    }

    public function deleteInstallerAction(): Action
    {
        return Action::make('deleteInstaller')
            ->label((string) __('capell-admin::button.delete_installer'))
            ->color('danger')
            ->outlined()
            ->size('sm')
            ->requiresConfirmation()
            ->action(function (): mixed {
                $action = 'Capell\\Installer\\Actions\\RemoveInstallerPackageAction';

                if (! class_exists($action)) {
                    Notification::make()
                        ->danger()
                        ->title((string) __('capell-admin::message.installer_remove_unavailable'))
                        ->send();

                    return null;
                }

                return redirect()->to($action::run());
            });
    }

    /**
     * @return Collection<string, MessageData>
     */
    protected function buildAlerts(): Collection
    {
        $alerts = collect();

        $typeCount = Blueprint::query()->count();
        $typeExpected = count(BlueprintSubjectEnum::cases());

        if ($typeCount < $typeExpected) {
            $alerts->put('blueprints', new MessageData(
                title: __('capell-admin::message.type_missing_heading'),
                message: __('capell-admin::message.type_missing_warning'),
                type: AlertTypeEnum::Warning,
                icon: 'heroicon-o-exclamation-triangle',
                action: $this->getAction('createType'),
            ));
        }

        $themeExists = Theme::query()->exists();
        $hasFoundationTheme = Theme::query()->default()->exists();

        if (! $themeExists) {
            $alerts->put('theme', new MessageData(
                title: __('capell-admin::message.theme_missing_heading'),
                message: __('capell-admin::message.theme_missing_warning'),
                type: AlertTypeEnum::Danger,
                icon: 'heroicon-o-exclamation-circle',
                action: $this->getAction('createTheme'),
            ));
        } elseif (! $hasFoundationTheme) {
            $alerts->put('theme', new MessageData(
                title: __('capell-admin::message.theme_no_default_heading'),
                message: __('capell-admin::message.theme_no_default_warning'),
                type: AlertTypeEnum::Warning,
                icon: 'heroicon-o-exclamation-triangle',
                action: $this->getAction('createTheme'),
            ));
        }

        $languageExists = Language::query()->exists();
        $hasDefaultLanguage = Language::query()->default()->exists();

        if (! $languageExists) {
            $alerts->put('language', new MessageData(
                title: __('capell-admin::message.language_missing_heading'),
                message: __('capell-admin::message.language_missing_warning'),
                type: AlertTypeEnum::Danger,
                icon: 'heroicon-o-exclamation-circle',
                action: $this->getAction('createLanguage'),
            ));
        } elseif (! $hasDefaultLanguage) {
            $alerts->put('language', new MessageData(
                title: __('capell-admin::message.language_no_default_heading'),
                message: __('capell-admin::message.language_no_default_warning'),
                type: AlertTypeEnum::Warning,
                icon: 'heroicon-o-exclamation-triangle',
                action: $this->getAction('createLanguage'),
            ));
        }

        $hasSite = Site::query()->exists();

        if (! $hasSite) {
            $siteAlertType = (! $themeExists || ! $hasFoundationTheme) ? AlertTypeEnum::Danger : AlertTypeEnum::Warning;

            $alerts->put('site', new MessageData(
                title: __('capell-admin::message.site_missing_heading'),
                message: __('capell-admin::message.site_missing_warning'),
                type: $siteAlertType,
                icon: $siteAlertType === AlertTypeEnum::Danger ? 'heroicon-o-exclamation-circle' : 'heroicon-o-exclamation-triangle',
                action: $this->getAction('createSite'),
            ));
        }

        $hasAllTypes = $typeCount >= $typeExpected;

        if ($hasAllTypes && $hasFoundationTheme && $hasDefaultLanguage && $hasSite && class_exists(InstallerServiceProvider::class)) {
            $alerts->put('installer', new MessageData(
                title: __('capell-admin::message.installer_present_heading'),
                message: __('capell-admin::message.installer_present_warning'),
                type: AlertTypeEnum::Info,
                icon: 'heroicon-o-information-circle',
                action: array_values(array_filter([
                    $this->getAction('viewInstaller'),
                    $this->getAction('deleteInstaller'),
                ])),
            ));
        }

        return $alerts;
    }
}
