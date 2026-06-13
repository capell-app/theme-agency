<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Providers;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Capell\CustomerPortal\Models\PortalSupportRequestReply;
use Capell\CustomerPortal\Support\PortalDashboardItemRegistry;
use Capell\CustomerPortal\Support\PortalPreferencesProviderRegistry;
use Capell\CustomerPortal\Support\PortalProfileProviderRegistry;
use Capell\CustomerPortal\Support\PortalSelfServiceItemRegistry;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Override;
use Spatie\LaravelPackageTools\Package;

final class CustomerPortalServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-customer-portal';

    public static string $packageName = 'capell-app/customer-portal';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile('capell-customer-portal')
            ->hasTranslations()
            ->hasViews()
            ->hasRoute('web')
            ->hasMigrations([
                '2026_05_31_150000_01_create_portal_accounts_table',
                '2026_05_31_150000_02_create_portal_support_requests_table',
                '2026_06_06_000001_create_portal_support_request_replies_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->register(AdminServiceProvider::class);

        $this->app->singleton(PortalDashboardItemRegistry::class);
        $this->app->singleton(PortalProfileProviderRegistry::class);
        $this->app->singleton(PortalPreferencesProviderRegistry::class);
        $this->app->singleton(PortalSelfServiceItemRegistry::class);

        $this->app->booted(function (): void {
            $this->registerRateLimiters();

            if (! $this->isPackageInstalled()) {
                return;
            }

            $this
                ->registerModels()
                ->registerProtectedTables();
        });
    }

    public function packageBooted(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        Relation::morphMap([
            'portal_account' => PortalAccount::class,
            'portal_support_request' => PortalSupportRequest::class,
            'portal_support_request_reply' => PortalSupportRequestReply::class,
        ], merge: true);
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerRateLimiters(): self
    {
        RateLimiter::for('capell-customer-portal-preferences', function (Request $request): Limit {
            $identifier = $request->user()?->getAuthIdentifier();
            $actorKey = is_scalar($identifier) ? (string) $identifier : 'guest';
            $key = hash('sha256', $actorKey . '|' . ($request->ip() ?? 'unknown'));

            return Limit::perMinute(20)->by($key);
        });

        return $this;
    }

    private function registerModels(): self
    {
        CapellCore::registerModels([
            PortalAccount::class,
            PortalSupportRequest::class,
            PortalSupportRequestReply::class,
        ]);

        return $this;
    }

    private function registerProtectedTables(): self
    {
        $tables = config('capell-customer-portal.tables', []);

        if (! is_array($tables)) {
            return $this;
        }

        foreach ($tables as $tableName) {
            if (! is_string($tableName)) {
                continue;
            }

            if ($tableName === '') {
                continue;
            }

            CapellCore::registerProtectedTable(static fn (): string => $tableName);
        }

        return $this;
    }
}
