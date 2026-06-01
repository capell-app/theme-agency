<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Providers;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\PrivacyCenter\Models\ConsentPolicy;
use Capell\PrivacyCenter\Models\ConsentRecord;
use Capell\PrivacyCenter\Models\PolicyAcceptance;
use Capell\PrivacyCenter\Models\PrivacyRequest;
use Capell\PrivacyCenter\Models\RetentionRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Override;
use Spatie\LaravelPackageTools\Package;

final class PrivacyCenterServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-privacy-center';

    public static string $packageName = 'capell-app/privacy-center';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile(self::$name)
            ->hasTranslations()
            ->hasMigrations([
                '2026_05_31_000001_create_privacy_consent_policies_table',
                '2026_05_31_000002_create_privacy_consent_records_table',
                '2026_05_31_000003_create_privacy_policy_acceptances_table',
                '2026_05_31_000004_create_privacy_retention_rules_table',
                '2026_05_31_000005_create_privacy_requests_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->booted(function (): void {
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
            'privacy_consent_policy' => ConsentPolicy::class,
            'privacy_consent_record' => ConsentRecord::class,
            'privacy_policy_acceptance' => PolicyAcceptance::class,
            'privacy_request' => PrivacyRequest::class,
            'privacy_retention_rule' => RetentionRule::class,
        ], merge: true);
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerModels(): self
    {
        CapellCore::registerModels([
            ConsentPolicy::class,
            ConsentRecord::class,
            PolicyAcceptance::class,
            PrivacyRequest::class,
            RetentionRule::class,
        ]);

        return $this;
    }

    private function registerProtectedTables(): self
    {
        $tables = config('capell-privacy-center.tables', []);

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
