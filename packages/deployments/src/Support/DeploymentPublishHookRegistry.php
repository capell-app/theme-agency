<?php

declare(strict_types=1);

namespace Capell\Deployments\Support;

use Capell\Deployments\Data\ComposerRequirementData;
use Capell\Deployments\Data\PublishComposerChangeResultData;
use Capell\Deployments\Models\DeploymentConnection;
use Throwable;

final class DeploymentPublishHookRegistry
{
    /** @var list<callable(DeploymentConnection, ComposerRequirementData): void> */
    private array $beforeHooks = [];

    /** @var list<callable(DeploymentConnection, ComposerRequirementData, PublishComposerChangeResultData, string|null): void> */
    private array $afterHooks = [];

    /** @var list<callable(DeploymentConnection, ComposerRequirementData, Throwable): void> */
    private array $failedHooks = [];

    /**
     * @param  callable(DeploymentConnection, ComposerRequirementData): void  $hook
     */
    public function beforePublish(callable $hook): void
    {
        $this->beforeHooks[] = $hook;
    }

    /**
     * @param  callable(DeploymentConnection, ComposerRequirementData, PublishComposerChangeResultData, string|null): void  $hook
     */
    public function afterPublish(callable $hook): void
    {
        $this->afterHooks[] = $hook;
    }

    /**
     * @param  callable(DeploymentConnection, ComposerRequirementData, Throwable): void  $hook
     */
    public function publishFailed(callable $hook): void
    {
        $this->failedHooks[] = $hook;
    }

    public function runBeforePublish(DeploymentConnection $connection, ComposerRequirementData $requirement): void
    {
        foreach ($this->beforeHooks as $hook) {
            $hook($connection, $requirement);
        }
    }

    public function runAfterPublish(
        DeploymentConnection $connection,
        ComposerRequirementData $requirement,
        PublishComposerChangeResultData $result,
        ?string $status = null,
    ): void {
        foreach ($this->afterHooks as $hook) {
            $hook($connection, $requirement, $result, $status);
        }
    }

    public function runPublishFailed(
        DeploymentConnection $connection,
        ComposerRequirementData $requirement,
        Throwable $exception,
    ): void {
        foreach ($this->failedHooks as $hook) {
            $hook($connection, $requirement, $exception);
        }
    }
}
