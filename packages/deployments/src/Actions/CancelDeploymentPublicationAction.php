<?php

declare(strict_types=1);

namespace Capell\Deployments\Actions;

use Capell\Deployments\Models\DeploymentConnection;
use Capell\Deployments\Models\DeploymentPublication;
use Capell\Deployments\Services\GitProvider\GitProviderFactory;
use LogicException;
use Lorisleiva\Actions\Concerns\AsAction;

final class CancelDeploymentPublicationAction
{
    use AsAction;

    public function __construct(private readonly GitProviderFactory $factory) {}

    public function handle(DeploymentPublication $publication, DeploymentConnection $connection): DeploymentPublication
    {
        if ($publication->deployment_connection_id !== $connection->id) {
            throw new LogicException('Deployment publication does not belong to the selected deployment connection.');
        }

        if ($publication->pull_request_id === null) {
            throw new LogicException('Only pull-request deployment publications can be cancelled.');
        }

        if (in_array($publication->status, ['cancelled', 'dry_run', 'success'], true)) {
            return $publication;
        }

        $this->factory->for($connection)->closePullRequest($connection, $publication->pull_request_id);

        $publication->forceFill([
            'status' => 'cancelled',
            'status_checked_at' => now(),
        ])->save();

        return $publication->refresh();
    }
}
