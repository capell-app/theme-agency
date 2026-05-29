<?php

declare(strict_types=1);

namespace Capell\Deployments\Contracts;

use Capell\Deployments\Data\PullRequestData;
use Capell\Deployments\Data\RepoFile;
use Capell\Deployments\Models\DeploymentConnection;

interface GitProviderContract
{
    public function getFile(DeploymentConnection $conn, string $path): RepoFile;

    public function getBranchCommitSha(DeploymentConnection $conn, string $branch): string;

    /** @param array<int, RepoFile> $files */
    public function commitFiles(
        DeploymentConnection $conn,
        string $branch,
        string $commitMessage,
        array $files,
    ): string;

    public function createBranch(DeploymentConnection $conn, string $branchName, string $fromCommitSha): void;

    public function openPullRequest(
        DeploymentConnection $conn,
        string $headBranch,
        string $title,
        string $body,
    ): PullRequestData;

    public function enableAutoMerge(DeploymentConnection $conn, int|string $pullRequestId): void;

    public function getPullRequest(DeploymentConnection $conn, int|string $pullRequestId): PullRequestData;

    public function closePullRequest(DeploymentConnection $conn, int|string $pullRequestId): void;

    /** Returns 'success', 'failure', or 'pending' */
    public function getDeployStatus(DeploymentConnection $conn, string $commitSha): string;
}
