<?php

declare(strict_types=1);

namespace Capell\Deployments\Actions;

use Capell\Deployments\Contracts\GitProviderContract;
use Capell\Deployments\Data\ComposerRequirementData;
use Capell\Deployments\Data\PublishComposerChangeResultData;
use Capell\Deployments\Data\PullRequestData;
use Capell\Deployments\Enums\InstallPolicy;
use Capell\Deployments\Events\DeploymentPublishFailed;
use Capell\Deployments\Events\DeploymentPublishSucceeded;
use Capell\Deployments\Models\DeploymentConnection;
use Capell\Deployments\Services\GitProvider\GitProviderFactory;
use Capell\Deployments\Support\DeploymentPublishHookRegistry;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

/**
 * @method static PublishComposerChangeResultData run(ComposerRequirementData $requirement, DeploymentConnection $connection, bool $dryRun = false)
 */
final class PublishComposerRequirementAction
{
    use AsAction;

    public function __construct(private readonly GitProviderFactory $factory) {}

    public function handle(
        ComposerRequirementData $requirement,
        DeploymentConnection $connection,
        bool $dryRun = false,
    ): PublishComposerChangeResultData {
        $hooks = app(DeploymentPublishHookRegistry::class);

        try {
            $hooks->runBeforePublish($connection, $requirement);
            $publication = $this->publish($requirement, $connection, $dryRun);
            $hooks->runAfterPublish($connection, $requirement, $publication['result'], $publication['status']);

            return $publication['result'];
        } catch (Throwable $exception) {
            $hooks->runPublishFailed($connection, $requirement, $exception);
            event(DeploymentPublishFailed::fromThrowable($connection, $requirement, $exception));

            throw $exception;
        }
    }

    /**
     * @return array{result: PublishComposerChangeResultData, status: string|null}
     */
    private function publish(
        ComposerRequirementData $requirement,
        DeploymentConnection $connection,
        bool $dryRun,
    ): array {
        $provider = $this->factory->for($connection);
        $slug = str($requirement->label ?? $requirement->composerName)->afterLast('/')->slug()->toString();

        $composerJson = $provider->getFile($connection, 'composer.json');
        $patched = PrepareComposerRequirementCommitAction::run($requirement, $composerJson);

        if ($connection->install_policy === InstallPolicy::DirectCommit) {
            if ($dryRun) {
                $result = new PublishComposerChangeResultData(provider: $connection->provider, dryRun: true);

                RecordDeploymentPublicationAction::run($connection, $requirement, $result);
                event(new DeploymentPublishSucceeded($connection, $requirement, $result));

                return ['result' => $result, 'status' => null];
            }

            $sha = $provider->commitFiles(
                $connection,
                $connection->default_branch,
                'Add extension ' . $requirement->composerName,
                [$patched],
            );

            $result = new PublishComposerChangeResultData(provider: $connection->provider, commitSha: $sha);
            $status = $provider->getDeployStatus($connection, $sha);

            RecordDeploymentPublicationAction::run($connection, $requirement, $result, $status);
            event(new DeploymentPublishSucceeded($connection, $requirement, $result, $status));

            return ['result' => $result, 'status' => $status];
        }

        $branchName = 'capell/add-extension-' . $slug;
        $existingPullRequest = $provider->findOpenPullRequestForBranch($connection, $branchName);

        if ($existingPullRequest instanceof PullRequestData) {
            $status = $existingPullRequest->headSha !== ''
                ? $provider->getDeployStatus($connection, $existingPullRequest->headSha)
                : 'pending';

            if (! $dryRun && $connection->install_policy === InstallPolicy::PullRequestAutoMerge && $status === 'success') {
                $provider->enableAutoMerge($connection, $existingPullRequest->id);
            }

            $result = new PublishComposerChangeResultData(
                provider: $connection->provider,
                pullRequestUrl: $existingPullRequest->url,
                commitSha: $existingPullRequest->headSha !== '' ? $existingPullRequest->headSha : null,
                pullRequestId: is_int($existingPullRequest->id) ? $existingPullRequest->id : null,
                dryRun: $dryRun,
                branchName: $branchName,
            );

            RecordDeploymentPublicationAction::run($connection, $requirement, $result, $status);
            event(new DeploymentPublishSucceeded($connection, $requirement, $result, $status));

            return ['result' => $result, 'status' => $status];
        }

        if ($dryRun) {
            $result = new PublishComposerChangeResultData(provider: $connection->provider, dryRun: true, branchName: $branchName);

            RecordDeploymentPublicationAction::run($connection, $requirement, $result);
            event(new DeploymentPublishSucceeded($connection, $requirement, $result));

            return ['result' => $result, 'status' => null];
        }

        $this->ensureBranchExists($provider, $connection, $branchName);
        $commitSha = $provider->commitFiles($connection, $branchName, 'Add extension ' . $requirement->composerName, [$patched]);

        $pr = $provider->openPullRequest(
            $connection,
            $branchName,
            sprintf('Add extension %s', $requirement->composerName),
            "Auto-generated by Capell.\n\nThis PR adds `{$requirement->composerName}` to your Capell instance.",
        );

        $status = $provider->getDeployStatus($connection, $commitSha);

        $result = new PublishComposerChangeResultData(
            provider: $connection->provider,
            pullRequestUrl: $pr->url,
            commitSha: $commitSha,
            pullRequestId: is_int($pr->id) ? $pr->id : null,
            branchName: $branchName,
        );

        if ($connection->install_policy === InstallPolicy::PullRequestAutoMerge && $status === 'success') {
            $provider->enableAutoMerge($connection, $pr->id);
        }

        RecordDeploymentPublicationAction::run($connection, $requirement, $result, $status);
        event(new DeploymentPublishSucceeded($connection, $requirement, $result, $status));

        return ['result' => $result, 'status' => $status];
    }

    private function ensureBranchExists(GitProviderContract $provider, DeploymentConnection $connection, string $branchName): void
    {
        try {
            $provider->getBranchCommitSha($connection, $branchName);

            return;
        } catch (Throwable) {
            //
        }

        $provider->createBranch($connection, $branchName, $provider->getBranchCommitSha($connection, $connection->default_branch));
    }
}
