<?php

declare(strict_types=1);

namespace Capell\Deployments\Services\GitProvider;

use Capell\Deployments\Actions\RefreshProviderTokenAction;
use Capell\Deployments\Contracts\GitProviderContract;
use Capell\Deployments\Data\PullRequestData;
use Capell\Deployments\Data\RepoFile;
use Capell\Deployments\Models\DeploymentConnection;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;

final class BitbucketProvider implements GitProviderContract
{
    public function __construct(private readonly Factory $http) {}

    public function getFile(DeploymentConnection $conn, string $path): RepoFile
    {
        $content = $this->client($conn)
            ->retry(2, 200, throw: false)
            ->get(sprintf(
                '/repositories/%s/%s/src/%s/%s',
                $conn->repo_owner,
                $conn->repo_name,
                $conn->default_branch,
                $path,
            ))
            ->throw()
            ->body();

        return new RepoFile(
            path: $path,
            content: $content,
            sha: '',
        );
    }

    public function getBranchCommitSha(DeploymentConnection $conn, string $branch): string
    {
        $branchData = $this->client($conn)
            ->retry(2, 200, throw: false)
            ->get(sprintf('/repositories/%s/%s/refs/branches/%s', $conn->repo_owner, $conn->repo_name, $branch))
            ->throw()
            ->json();

        return (string) $branchData['target']['hash'];
    }

    /**
     * @param  array<int, RepoFile>  $files
     */
    public function commitFiles(
        DeploymentConnection $conn,
        string $branch,
        string $commitMessage,
        array $files,
    ): string {
        $owner = $conn->repo_owner;
        $repo = $conn->repo_name;

        $request = $this->client($conn)
            ->asMultipart()
            ->attach('message', $commitMessage)
            ->attach('branch', $branch);

        foreach ($files as $repoFile) {
            $request = $request->attach($repoFile->path, $repoFile->content);
        }

        $request->post(sprintf('/repositories/%s/%s/src', $owner, $repo))->throw();

        $branchData = $this->client($conn)
            ->retry(2, 200, throw: false)
            ->get(sprintf('/repositories/%s/%s/refs/branches/%s', $owner, $repo, $branch))
            ->throw()
            ->json();

        return $branchData['target']['hash'];
    }

    public function createBranch(DeploymentConnection $conn, string $branchName, string $fromCommitSha): void
    {
        $this->client($conn)
            ->post(sprintf('/repositories/%s/%s/refs/branches', $conn->repo_owner, $conn->repo_name), [
                'name' => $branchName,
                'target' => ['hash' => $fromCommitSha],
            ])
            ->throw();
    }

    public function openPullRequest(
        DeploymentConnection $conn,
        string $headBranch,
        string $title,
        string $body,
    ): PullRequestData {
        $response = $this->client($conn)
            ->post(sprintf('/repositories/%s/%s/pullrequests', $conn->repo_owner, $conn->repo_name), [
                'title' => $title,
                'description' => $body,
                'source' => ['branch' => ['name' => $headBranch]],
                'destination' => ['branch' => ['name' => $conn->default_branch]],
                'close_source_branch' => true,
            ])
            ->throw()
            ->json();
        $response = $this->responseMap($response);

        return $this->pullRequestDataFromResponse($response);
    }

    public function findOpenPullRequestForBranch(DeploymentConnection $conn, string $headBranch): ?PullRequestData
    {
        $response = $this->client($conn)
            ->retry(2, 200, throw: false)
            ->get(sprintf('/repositories/%s/%s/pullrequests', $conn->repo_owner, $conn->repo_name), [
                'q' => sprintf('source.branch.name="%s" AND state="OPEN"', $headBranch),
            ])
            ->throw()
            ->json();
        $response = $this->responseMap($response);

        $pullRequests = $this->responseList($response['values'] ?? []);

        if (! isset($pullRequests[0])) {
            return null;
        }

        return $this->pullRequestDataFromResponse($pullRequests[0]);
    }

    public function enableAutoMerge(DeploymentConnection $conn, int|string $pullRequestId): void
    {
        Log::warning('BitbucketProvider: auto-merge is not natively supported by Bitbucket Cloud.', [
            'repo' => $conn->repoCoordinate(),
            'pull_request_id' => $pullRequestId,
        ]);
    }

    public function getPullRequest(DeploymentConnection $conn, int|string $pullRequestId): PullRequestData
    {
        $response = $this->client($conn)
            ->retry(2, 200, throw: false)
            ->get(sprintf('/repositories/%s/%s/pullrequests/%s', $conn->repo_owner, $conn->repo_name, $pullRequestId))
            ->throw()
            ->json();
        $response = $this->responseMap($response);

        return $this->pullRequestDataFromResponse($response);
    }

    public function closePullRequest(DeploymentConnection $conn, int|string $pullRequestId): void
    {
        $this->client($conn)
            ->post(sprintf('/repositories/%s/%s/pullrequests/%s/decline', $conn->repo_owner, $conn->repo_name, $pullRequestId))
            ->throw();
    }

    public function getDeployStatus(DeploymentConnection $conn, string $commitSha): string
    {
        $response = $this->client($conn)
            ->retry(2, 200, throw: false)
            ->get(sprintf('/repositories/%s/%s/commit/%s/statuses', $conn->repo_owner, $conn->repo_name, $commitSha))
            ->throw()
            ->json();
        $response = $this->responseMap($response);

        $statuses = $this->responseList($response['values'] ?? []);

        foreach ($statuses as $status) {
            if (($status['state'] ?? null) === 'FAILED') {
                return 'failure';
            }
        }

        foreach ($statuses as $status) {
            if (($status['state'] ?? null) === 'INPROGRESS') {
                return 'pending';
            }
        }

        return 'success';
    }

    private function client(DeploymentConnection $conn): PendingRequest
    {
        $conn = RefreshProviderTokenAction::run($conn);

        return $this->http
            ->baseUrl('https://api.bitbucket.org/2.0')
            ->withToken($conn->access_token_encrypted)
            ->retry(2, 200, throw: false)
            ->timeout($this->httpTimeout())
            ->connectTimeout(5);
    }

    private function httpTimeout(): int
    {
        $timeout = config('capell-deployments.http_timeout', 10);

        if (is_numeric($timeout)) {
            return max(1, (int) $timeout);
        }

        return 10;
    }

    /** @param array<string, mixed> $response */
    private function pullRequestDataFromResponse(array $response): PullRequestData
    {
        return new PullRequestData(
            id: $response['id'],
            url: $response['links']['html']['href'],
            state: strtolower((string) $response['state']),
            headBranch: $response['source']['branch']['name'],
            baseBranch: $response['destination']['branch']['name'],
            headSha: $response['source']['commit']['hash'],
            merged: $response['state'] === 'MERGED',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function responseMap(mixed $response): array
    {
        if (! is_array($response)) {
            return [];
        }

        $map = [];

        foreach ($response as $key => $value) {
            if (is_string($key)) {
                $map[$key] = $value;
            }
        }

        return $map;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function responseList(mixed $response): array
    {
        if (! is_array($response)) {
            return [];
        }

        $items = [];

        foreach ($response as $item) {
            if (is_array($item)) {
                $items[] = $this->responseMap($item);
            }
        }

        return $items;
    }
}
