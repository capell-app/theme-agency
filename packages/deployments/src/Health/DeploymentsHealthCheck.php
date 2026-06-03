<?php

declare(strict_types=1);

namespace Capell\Deployments\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Deployments\Enums\GitProviderType;
use Capell\Deployments\Models\DeploymentConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class DeploymentsHealthCheck implements ChecksExtensionHealth
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
            $check->storageTableCheck(),
            $check->oauthProviderConfigurationCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts the connection storage table exists so connections can be persisted.
     */
    public function storageTableCheck(): DoctorCheckResultData
    {
        $tableExists = $this->hasConnectionTable();

        return new DoctorCheckResultData(
            label: 'Deployments storage table',
            passed: $tableExists,
            message: $tableExists
                ? 'The deployment_connections table is present.'
                : 'The deployment_connections table is missing.',
            remediation: $tableExists
                ? null
                : 'Run the Capell migrations to create the deployment_connections table.',
        );
    }

    /**
     * Asserts at least one Git provider has an OAuth client id configured, so an
     * operator can connect a repository. Reports only provider names and never
     * the configured credential values themselves.
     */
    public function oauthProviderConfigurationCheck(): DoctorCheckResultData
    {
        $configuredProviders = $this->configuredProviderNames();

        return new DoctorCheckResultData(
            label: 'Deployments OAuth provider configuration',
            passed: $configuredProviders !== [],
            message: $configuredProviders === []
                ? 'No Git provider OAuth client is configured; repositories cannot be connected.'
                : 'OAuth client configured for: ' . implode(', ', $configuredProviders) . '.',
            remediation: $configuredProviders === []
                ? 'Set a CAPELL_<PROVIDER>_CLIENT_ID and CAPELL_<PROVIDER>_CLIENT_SECRET for at least one Git provider.'
                : null,
        );
    }

    public function hasConnectionTable(): bool
    {
        /** @var Model $connection */
        $connection = new DeploymentConnection;

        return Schema::hasTable($connection->getTable());
    }

    /**
     * @return list<string>
     */
    public function configuredProviderNames(): array
    {
        return collect(GitProviderType::cases())
            ->filter(fn (GitProviderType $provider): bool => $this->hasConfiguredClientId($provider))
            ->map(static fn (GitProviderType $provider): string => $provider->getLabel())
            ->values()
            ->all();
    }

    public function hasConfiguredClientId(GitProviderType $provider): bool
    {
        $clientId = config(sprintf('capell-deployments.oauth.%s.client_id', $provider->value));

        return is_string($clientId) && $clientId !== '';
    }
}
