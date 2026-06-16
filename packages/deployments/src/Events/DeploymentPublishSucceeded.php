<?php

declare(strict_types=1);

namespace Capell\Deployments\Events;

use Capell\Deployments\Data\ComposerRequirementData;
use Capell\Deployments\Data\PublishComposerChangeResultData;
use Capell\Deployments\Models\DeploymentConnection;

final class DeploymentPublishSucceeded
{
    public function __construct(
        public readonly DeploymentConnection $connection,
        public readonly ComposerRequirementData $requirement,
        public readonly PublishComposerChangeResultData $result,
        public readonly ?string $status = null,
    ) {}
}
