<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Providers;

use Capell\Admin\Data\Extensions\ExtensionManagementSurfaceData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Support\Notifications\AdminNotificationGroupRegistry;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\LoginAudit\Actions\ApplyLoginAuditSettingsAction;
use Capell\LoginAudit\Actions\SendLoginAuditAdminAlertAction;
use Capell\LoginAudit\Filament\Settings\LoginAuditSettingsSchema;
use Capell\LoginAudit\Http\Middleware\UserActivityMiddleware;
use Capell\LoginAudit\Listeners\DetectSuspiciousLoginFromAuthEvent;
use Capell\LoginAudit\Models\LoginAudit;
use Capell\LoginAudit\Observers\LoginAuditObserver;
use Capell\LoginAudit\Policies\LoginAuditPolicy;
use Capell\LoginAudit\Settings\LoginAuditSettings;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Override;
use Rappasoft\LaravelAuthenticationLog\Models\AuthenticationLog as VendorLoginAudit;
use Spatie\LaravelPackageTools\Package;

final class LoginAuditServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-login-audit';

    public static string $packageName = 'capell-app/login-audit';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile('login-audit')
            ->hasTranslations()
            ->hasMigrations([
                '2026_05_10_190857_01_create_login_audit_table',
            ]);
    }

    public function registeringPackage(): void
    {
        $this->syncVendorAuthenticationLogConfiguration();
        $this->app->register(AdminServiceProvider::class);
    }

    public function packageRegistered(): void
    {
        $this->app->booted(function (): void {
            if (! $this->isPackageInstalled()) {
                return;
            }

            $this
                ->registerModels()
                ->registerPolicies()
                ->registerSettings()
                ->syncSettings()
                ->registerEventListeners()
                ->registerNotificationGroups()
                ->registerProtectedTables()
                ->registerMiddlewareAliases();
        });
    }

    public function packageBooted(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        VendorLoginAudit::observe(LoginAuditObserver::class);
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerModels(): self
    {
        Config::set('login-audit.login_audit_model', LoginAudit::class);
        $this->syncVendorAuthenticationLogConfiguration();

        $this->surface()->models([LoginAudit::class]);

        return $this;
    }

    private function registerPolicies(): self
    {
        Gate::policy(LoginAudit::class, LoginAuditPolicy::class);

        return $this;
    }

    private function registerSettings(): self
    {
        $this->surface()->settingsClass('login_audit', LoginAuditSettings::class);
        $this->surface()->settingsSchema('login_audit', LoginAuditSettingsSchema::class);
        CapellAdmin::registerExtensionManagementSurface(ExtensionManagementSurfaceData::settings(
            packageName: self::$packageName,
            label: 'capell-login-audit::settings.security_access',
            settingsGroup: 'login_audit',
            icon: 'heroicon-o-shield-check',
        ));

        return $this;
    }

    private function syncSettings(): self
    {
        ApplyLoginAuditSettingsAction::run();
        $this->syncVendorAuthenticationLogConfiguration();

        return $this;
    }

    private function registerEventListeners(): self
    {
        Event::listen(Login::class, DetectSuspiciousLoginFromAuthEvent::class);
        Event::listen(Failed::class, DetectSuspiciousLoginFromAuthEvent::class);

        return $this;
    }

    private function registerNotificationGroups(): self
    {
        $this->app->afterResolving(AdminNotificationGroupRegistry::class, function (AdminNotificationGroupRegistry $registry): void {
            $registry->register(
                key: SendLoginAuditAdminAlertAction::NOTIFICATION_GROUP,
                label: (string) __('capell-login-audit::settings.alert_group_label'),
                description: (string) __('capell-login-audit::settings.alert_group_description'),
                defaultRecipients: fn (): EloquentCollection => $this->defaultAlertRecipients(),
            );
        });

        return $this;
    }

    /**
     * @return EloquentCollection<int, Model>
     */
    private function defaultAlertRecipients(): EloquentCollection
    {
        $userModel = config('auth.providers.users.model');

        if (! is_string($userModel) || ! is_a($userModel, Model::class, true)) {
            return new EloquentCollection;
        }

        return $userModel::query()
            ->get()
            ->filter(function (Model $user): bool {
                if (method_exists($user, 'isGlobalAdmin') && $user->isGlobalAdmin()) {
                    return true;
                }

                return method_exists($user, 'hasRole')
                    && $user->hasRole(config('capell.roles.super_admin', 'super_admin'));
            })
            ->values();
    }

    private function registerProtectedTables(): self
    {
        CapellCore::registerProtectedTable(fn (): string => config('login-audit.table_name', 'login_audit'));

        return $this;
    }

    private function registerMiddlewareAliases(): self
    {
        Route::aliasMiddleware('frontend.activity', UserActivityMiddleware::class);

        return $this;
    }

    private function syncVendorAuthenticationLogConfiguration(): void
    {
        Config::set('authentication-log.table_name', config('login-audit.table_name', 'login_audit'));
        Config::set('authentication-log.db_connection', config('login-audit.db_connection'));
        Config::set('authentication-log.events', config('login-audit.events', []));
        Config::set('authentication-log.listeners', config('login-audit.listeners', []));
        Config::set('authentication-log.notifications', config('login-audit.notifications', []));
        Config::set('authentication-log.suspicious', config('login-audit.suspicious', []));
        Config::set('authentication-log.purge', config('login-audit.purge', 365));
        Config::set('authentication-log.behind_cdn', config('login-audit.behind_cdn', false));
    }
}
