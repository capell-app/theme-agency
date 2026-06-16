<?php

declare(strict_types=1);

namespace Capell\Deployments\Events;

use Capell\Deployments\Data\ComposerRequirementData;
use Capell\Deployments\Models\DeploymentConnection;
use Throwable;

final class DeploymentPublishFailed
{
    public function __construct(
        public readonly DeploymentConnection $connection,
        public readonly ComposerRequirementData $requirement,
        public readonly string $exceptionClass,
        public readonly string $message,
    ) {}

    public static function fromThrowable(
        DeploymentConnection $connection,
        ComposerRequirementData $requirement,
        Throwable $exception,
    ): self {
        return new self(
            connection: $connection,
            requirement: $requirement,
            exceptionClass: $exception::class,
            message: $exception->getMessage(),
        );
    }
}
