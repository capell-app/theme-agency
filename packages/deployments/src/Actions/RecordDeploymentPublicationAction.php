<?php

declare(strict_types=1);

namespace Capell\Deployments\Actions;

use Capell\Deployments\Data\ComposerRequirementData;
use Capell\Deployments\Data\PublishComposerChangeResultData;
use Capell\Deployments\Models\DeploymentConnection;
use Capell\Deployments\Models\DeploymentPublication;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordDeploymentPublicationAction
{
    use AsAction;

    public function handle(
        DeploymentConnection $connection,
        ComposerRequirementData $requirement,
        PublishComposerChangeResultData $result,
        string $status = 'pending',
    ): DeploymentPublication {
        return DeploymentPublication::query()->create([
            'deployment_connection_id' => $connection->id,
            'provider' => $connection->provider,
            'repo_owner' => $connection->repo_owner,
            'repo_name' => $connection->repo_name,
            'composer_package' => $requirement->composerName,
            'constraint' => $requirement->versionConstraint,
            'branch_name' => $result->branchName,
            'commit_sha' => $result->commitSha,
            'pull_request_id' => $result->pullRequestId,
            'pull_request_url' => $result->pullRequestUrl,
            'status' => $result->dryRun ? 'dry_run' : $status,
            'dry_run' => $result->dryRun,
            'status_checked_at' => $result->commitSha === null ? null : now(),
        ]);
    }
}
