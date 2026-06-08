<?php

declare(strict_types=1);

namespace Capell\Deployments\Actions;

use Capell\Deployments\Models\DeploymentConnection;
use Capell\Deployments\Models\DeploymentPublication;
use Capell\Deployments\Services\GitProvider\GitProviderFactory;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static DeploymentPublication run(DeploymentPublication $publication, DeploymentConnection $connection)
 */
final class RefreshDeploymentPublicationStatusAction
{
    use AsAction;

    public function __construct(private readonly GitProviderFactory $factory) {}

    public function handle(DeploymentPublication $publication, DeploymentConnection $connection): DeploymentPublication
    {
        if ($publication->commit_sha === null || $publication->dry_run || in_array($publication->status, ['failure', 'success'], true)) {
            return $publication;
        }

        if ($publication->status_checked_at !== null && $publication->status_checked_at->isAfter(now()->subMinutes(5))) {
            return $publication;
        }

        $status = $this->factory->for($connection)->getDeployStatus($connection, $publication->commit_sha);

        $publication->forceFill([
            'status' => $status,
            'status_checked_at' => now(),
        ])->save();

        return $publication->refresh();
    }
}
