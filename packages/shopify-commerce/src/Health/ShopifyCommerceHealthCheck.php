<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class ShopifyCommerceHealthCheck implements ChecksExtensionHealth
{
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
            $check->syncStateCheck(),
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
            label: 'Shopify Commerce storage tables',
            passed: $missingTables === [],
            message: $missingTables === []
                ? 'Connection, OAuth state, catalog, variant, and customer cache tables are present.'
                : 'Missing tables: ' . implode(', ', $missingTables) . '.',
            remediation: $missingTables === []
                ? null
                : 'Run the Capell migrations to create the Shopify Commerce storage tables.',
        );
    }

    public function shopifyAppCredentialsCheck(): DoctorCheckResultData
    {
        $missingKeys = $this->missingAppCredentialKeys();

        return new DoctorCheckResultData(
            label: 'Shopify Commerce app credentials',
            passed: $missingKeys === [],
            message: $missingKeys === []
                ? 'Shopify OAuth client ID and client secret are configured.'
                : 'Missing Shopify app configuration: ' . implode(', ', $missingKeys) . '.',
            remediation: $missingKeys === []
                ? null
                : 'Set SHOPIFY_APP_CLIENT_ID and SHOPIFY_APP_CLIENT_SECRET for the Shopify app.',
        );
    }

    public function connectionCredentialsCheck(): DoctorCheckResultData
    {
        $missingTokenCount = $this->activeConnectionsMissingTokenCount();

        return new DoctorCheckResultData(
            label: 'Shopify Commerce connection credentials',
            passed: $missingTokenCount === 0,
            message: $missingTokenCount === 0
                ? 'Every active Shopify connection has a stored Admin API token.'
                : $missingTokenCount . ' active Shopify connection(s) are missing a stored Admin API token.',
            remediation: $missingTokenCount === 0
                ? null
                : 'Reconnect affected Shopify stores so encrypted Admin API tokens are stored again.',
        );
    }

    public function syncStateCheck(): DoctorCheckResultData
    {
        $staleSyncCount = $this->staleSyncOperationCount();

        return new DoctorCheckResultData(
            label: 'Shopify Commerce sync state',
            passed: $staleSyncCount === 0,
            message: $staleSyncCount === 0
                ? 'No queued, running, or importing Shopify sync operations appear stale.'
                : $staleSyncCount . ' Shopify sync operation(s) appear stale.',
            remediation: $staleSyncCount === 0
                ? null
                : 'Inspect the Shopify Commerce queue and rerun or clear stale sync operations from the admin.',
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
            ->where('status', 'active')
            ->where(static function (Builder $query): void {
                $query
                    ->whereNull('access_token')
                    ->orWhere('access_token', '');
            })
            ->count();
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
}
