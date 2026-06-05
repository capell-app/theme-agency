<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\ShopifyCommerce\Actions\Graphql\ExecuteShopifyAdminGraphqlAction;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class ShopifyCommerceHealthCheck implements ChecksExtensionHealth
{
    private const string TOKEN_PROBE_QUERY = <<<'GRAPHQL'
query capellShopifyCommerceHealthProbe {
  shop {
    name
  }
}
GRAPHQL;

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->storageTablesCheck(),
            $check->shopifyAppCredentialsCheck(),
            $check->connectionCredentialsCheck(),
            $check->tokenValidityCheck(),
            $check->syncStateCheck(),
            $check->catalogFreshnessCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    public function storageTablesCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables();

        return new DoctorCheckResultData(
            label: (string) __('capell-shopify-commerce::capell-shopify-commerce.health.storage_tables_label'),
            passed: $missingTables === [],
            message: $missingTables === []
                ? (string) __('capell-shopify-commerce::capell-shopify-commerce.health.storage_tables_passed')
                : (string) __('capell-shopify-commerce::capell-shopify-commerce.health.storage_tables_failed', [
                    'tables' => implode(', ', $missingTables),
                ]),
            remediation: $missingTables === []
                ? null
                : (string) __('capell-shopify-commerce::capell-shopify-commerce.health.storage_tables_remediation'),
        );
    }

    public function shopifyAppCredentialsCheck(): DoctorCheckResultData
    {
        $missingKeys = $this->missingAppCredentialKeys();

        return new DoctorCheckResultData(
            label: (string) __('capell-shopify-commerce::capell-shopify-commerce.health.app_credentials_label'),
            passed: $missingKeys === [],
            message: $missingKeys === []
                ? (string) __('capell-shopify-commerce::capell-shopify-commerce.health.app_credentials_passed')
                : (string) __('capell-shopify-commerce::capell-shopify-commerce.health.app_credentials_failed', [
                    'keys' => implode(', ', $missingKeys),
                ]),
            remediation: $missingKeys === []
                ? null
                : (string) __('capell-shopify-commerce::capell-shopify-commerce.health.app_credentials_remediation'),
        );
    }

    public function connectionCredentialsCheck(): DoctorCheckResultData
    {
        $missingTokenCount = $this->activeConnectionsMissingTokenCount();

        return new DoctorCheckResultData(
            label: (string) __('capell-shopify-commerce::capell-shopify-commerce.health.connection_credentials_label'),
            passed: $missingTokenCount === 0,
            message: $missingTokenCount === 0
                ? (string) __('capell-shopify-commerce::capell-shopify-commerce.health.connection_credentials_passed')
                : trans_choice(
                    'capell-shopify-commerce::capell-shopify-commerce.health.connection_credentials_failed',
                    $missingTokenCount,
                    ['count' => $missingTokenCount],
                ),
            remediation: $missingTokenCount === 0
                ? null
                : (string) __('capell-shopify-commerce::capell-shopify-commerce.health.connection_credentials_remediation'),
        );
    }

    public function tokenValidityCheck(): DoctorCheckResultData
    {
        $probeConnections = $this->activeTokenProbeConnections();
        $failedProbeCount = $probeConnections
            ->filter(fn (ShopifyConnection $connection): bool => ! $this->tokenProbePassed($connection))
            ->count();

        return new DoctorCheckResultData(
            label: (string) __('capell-shopify-commerce::capell-shopify-commerce.health.token_validity_label'),
            passed: $failedProbeCount === 0,
            message: $failedProbeCount === 0
                ? trans_choice(
                    'capell-shopify-commerce::capell-shopify-commerce.health.token_validity_passed',
                    $probeConnections->count(),
                    ['count' => $probeConnections->count()],
                )
                : trans_choice(
                    'capell-shopify-commerce::capell-shopify-commerce.health.token_validity_failed',
                    $failedProbeCount,
                    ['count' => $failedProbeCount],
                ),
            remediation: $failedProbeCount === 0
                ? null
                : (string) __('capell-shopify-commerce::capell-shopify-commerce.health.token_validity_remediation'),
        );
    }

    public function syncStateCheck(): DoctorCheckResultData
    {
        $staleSyncCount = $this->staleSyncOperationCount();

        return new DoctorCheckResultData(
            label: (string) __('capell-shopify-commerce::capell-shopify-commerce.health.sync_state_label'),
            passed: $staleSyncCount === 0,
            message: $staleSyncCount === 0
                ? (string) __('capell-shopify-commerce::capell-shopify-commerce.health.sync_state_passed')
                : trans_choice(
                    'capell-shopify-commerce::capell-shopify-commerce.health.sync_state_failed',
                    $staleSyncCount,
                    ['count' => $staleSyncCount],
                ),
            remediation: $staleSyncCount === 0
                ? null
                : (string) __('capell-shopify-commerce::capell-shopify-commerce.health.sync_state_remediation'),
        );
    }

    public function catalogFreshnessCheck(): DoctorCheckResultData
    {
        $staleCatalogConnectionCount = $this->staleCatalogConnectionCount();

        return new DoctorCheckResultData(
            label: (string) __('capell-shopify-commerce::capell-shopify-commerce.health.catalog_freshness_label'),
            passed: $staleCatalogConnectionCount === 0,
            message: $staleCatalogConnectionCount === 0
                ? (string) __('capell-shopify-commerce::capell-shopify-commerce.health.catalog_freshness_passed')
                : trans_choice(
                    'capell-shopify-commerce::capell-shopify-commerce.health.catalog_freshness_failed',
                    $staleCatalogConnectionCount,
                    ['count' => $staleCatalogConnectionCount],
                ),
            remediation: $staleCatalogConnectionCount === 0
                ? null
                : (string) __('capell-shopify-commerce::capell-shopify-commerce.health.catalog_freshness_remediation'),
        );
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return array_values(collect($this->requiredTableNames())
            ->reject(static fn (string $tableName): bool => Schema::hasTable($tableName))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function missingAppCredentialKeys(): array
    {
        $requiredConfigKeys = [
            'capell-shopify-commerce.client_id',
            'capell-shopify-commerce.client_secret',
        ];

        return array_values(collect($requiredConfigKeys)
            ->reject(static function (string $configKey): bool {
                $value = config($configKey);

                return is_string($value) && $value !== '';
            })
            ->values()
            ->all());
    }

    public function activeConnectionsMissingTokenCount(): int
    {
        if (! Schema::hasTable('shopify_connections')) {
            return 0;
        }

        return ShopifyConnection::query()
            ->where('status', ShopifyConnectionStatus::Active->value)
            ->where(static function (Builder $query): void {
                $query
                    ->whereNull('access_token')
                    ->orWhere('access_token', '');
            })
            ->count();
    }

    /**
     * @return Collection<int, ShopifyConnection>
     */
    public function activeTokenProbeConnections(): Collection
    {
        if (! Schema::hasTable('shopify_connections')) {
            return collect();
        }

        return ShopifyConnection::query()
            ->where('status', ShopifyConnectionStatus::Active->value)
            ->whereNotNull('access_token')
            ->where('access_token', '!=', '')
            ->orderBy('id')
            ->get()
            ->toBase();
    }

    public function staleSyncOperationCount(): int
    {
        if (! Schema::hasTable('shopify_connections')) {
            return 0;
        }

        $staleBefore = now()->subMinutes(30);

        return ShopifyConnection::query()
            ->where(static function (Builder $query) use ($staleBefore): void {
                $query
                    ->where(static function (Builder $runningQuery) use ($staleBefore): void {
                        $runningQuery
                            ->whereIn('sync_status', ['running', 'importing'])
                            ->where(function (Builder $timestampQuery) use ($staleBefore): void {
                                $timestampQuery
                                    ->whereNull('last_sync_started_at')
                                    ->orWhere('last_sync_started_at', '<', $staleBefore);
                            });
                    })
                    ->orWhere(static function (Builder $queuedQuery) use ($staleBefore): void {
                        $queuedQuery
                            ->where('sync_status', 'queued')
                            ->where(function (Builder $timestampQuery) use ($staleBefore): void {
                                $timestampQuery
                                    ->whereNull('last_sync_queued_at')
                                    ->orWhere('last_sync_queued_at', '<', $staleBefore);
                            });
                    });
            })
            ->count();
    }

    public function staleCatalogConnectionCount(): int
    {
        if (! Schema::hasTable('shopify_connections')) {
            return 0;
        }

        $staleBefore = now()->subHours($this->maxCatalogSyncAgeHours());

        return ShopifyConnection::query()
            ->where('status', ShopifyConnectionStatus::Active->value)
            ->where(static function (Builder $query): void {
                $query
                    ->whereNull('sync_status')
                    ->orWhereNotIn('sync_status', ['queued', 'running', 'importing']);
            })
            ->where(static function (Builder $query) use ($staleBefore): void {
                $query
                    ->whereNull('last_synced_at')
                    ->orWhere('last_synced_at', '<', $staleBefore);
            })
            ->count();
    }

    /**
     * @return list<string>
     */
    private function requiredTableNames(): array
    {
        return [
            'shopify_connections',
            'shopify_oauth_states',
            'shopify_products',
            'shopify_product_variants',
            'shopify_customers',
        ];
    }

    private function tokenProbePassed(ShopifyConnection $connection): bool
    {
        try {
            $payload = ExecuteShopifyAdminGraphqlAction::run($connection, self::TOKEN_PROBE_QUERY);
        } catch (Throwable) {
            return false;
        }

        return is_string(data_get($payload, 'data.shop.name'));
    }

    private function maxCatalogSyncAgeHours(): int
    {
        return max(1, (int) config('capell-shopify-commerce.health_max_catalog_sync_age_hours', 24));
    }
}
