<?php

declare(strict_types=1);

namespace Capell\Deployments\Actions;

use Capell\Deployments\Enums\GitProviderType;
use Capell\Deployments\Enums\InstallPolicy;
use Capell\Deployments\Models\DeploymentConnection;
use Lorisleiva\Actions\Concerns\AsAction;

final class ConnectDeploymentAction
{
    use AsAction;

    public function handle(
        GitProviderType $provider,
        string $repoOwner,
        string $repoName,
        string $accessToken,
        ?string $refreshToken = null,
        ?string $defaultBranch = 'main',
        InstallPolicy $installPolicy = InstallPolicy::PullRequestAutoMerge,
    ): DeploymentConnection {
        $connection = DeploymentConnection::query()->firstOrNew([
            'provider' => $provider->value,
            'repo_owner' => $repoOwner,
            'repo_name' => $repoName,
        ]);

        // Encrypted token columns are excluded from $fillable on the model so they
        // can never be assigned through Filament form state or HTTP input. Include
        // them in the same save as new rows because access_token_encrypted is
        // required at the database layer.
        $connection->forceFill([
            'default_branch' => $defaultBranch ?? 'main',
            'install_policy' => $installPolicy,
            'is_active' => true,
            'access_token_encrypted' => $accessToken,
            'refresh_token_encrypted' => $refreshToken,
        ])->save();

        return $connection;
    }
}
