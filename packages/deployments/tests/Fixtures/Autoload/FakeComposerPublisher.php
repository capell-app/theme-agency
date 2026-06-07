<?php

declare(strict_types=1);

namespace Capell\Deployments\Tests\Fixtures\Autoload;

use Capell\Deployments\Contracts\GitProviderContract;
use Capell\Deployments\Data\PullRequestData;
use Capell\Deployments\Data\RepoFile;
use Capell\Deployments\Models\DeploymentConnection;
use RuntimeException;

final class FakeComposerPublisher implements GitProviderContract
{
    /** @var list<array{branch: string, from: string}> */
    public array $branches = [];

    /** @var list<array{branch: string, message: string, files: array<int, RepoFile>}> */
    public array $commits = [];

    /** @var list<int|string> */
    public array $autoMergedPullRequestIds = [];

    public ?PullRequestData $existingPullRequest = null;

    public function getFile(DeploymentConnection $conn, string $path): RepoFile
    {
        return new RepoFile(
            path: $path,
            content: '{"require":{"php":"^8.3"}}',
            sha: 'composer-sha',
        );
    }

    public function getBranchCommitSha(DeploymentConnection $conn, string $branch): string
    {
        if ($branch !== $conn->default_branch && collect($this->branches)->doesntContain('branch', $branch)) {
            throw new RuntimeException('Branch does not exist.');
        }

        return 'branch-commit-sha';
    }

    public function commitFiles(DeploymentConnection $conn, string $branch, string $commitMessage, array $files): string
    {
        $this->commits[] = [
            'branch' => $branch,
            'message' => $commitMessage,
            'files' => $files,
        ];

        return 'commit-sha';
    }

    public function createBranch(DeploymentConnection $conn, string $branchName, string $fromCommitSha): void
    {
        $this->branches[] = [
            'branch' => $branchName,
            'from' => $fromCommitSha,
        ];
    }

    public function openPullRequest(DeploymentConnection $conn, string $headBranch, string $title, string $body): PullRequestData
    {
        return new PullRequestData(
            id: 123,
            url: 'https://github.test/pull/123',
            state: 'open',
            headBranch: $headBranch,
            baseBranch: $conn->default_branch,
            headSha: 'commit-sha',
            merged: false,
        );
    }

    public function findOpenPullRequestForBranch(DeploymentConnection $conn, string $headBranch): ?PullRequestData
    {
        return $this->existingPullRequest;
    }

    public function enableAutoMerge(DeploymentConnection $conn, int|string $pullRequestId): void
    {
        $this->autoMergedPullRequestIds[] = $pullRequestId;
    }

    public function getPullRequest(DeploymentConnection $conn, int|string $pullRequestId): PullRequestData
    {
        throw new RuntimeException('Not used in this test.');
    }

    public function closePullRequest(DeploymentConnection $conn, int|string $pullRequestId): void
    {
        throw new RuntimeException('Not used in this test.');
    }

    public function getDeployStatus(DeploymentConnection $conn, string $commitSha): string
    {
        throw new RuntimeException('Not used in this test.');
    }
}
