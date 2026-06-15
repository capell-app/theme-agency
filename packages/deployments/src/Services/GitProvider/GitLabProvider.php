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

final class GitLabProvider implements GitProviderContract
{
    public function __construct(private readonly Factory $http) {}

    public function getFile(DeploymentConnection $conn, string $path): RepoFile
    {
        $encodedProject = urlencode($conn->repoCoordinate());
        $encodedPath = rawurlencode($path);

        $response = $this->client($conn, retry: true)
            ->get(sprintf(
                '/projects/%s/repository/files/%s',
                $encodedProject,
                $encodedPath,
            ), ['ref' => $conn->default_branch])
            ->throw()
            ->json();

        $rawContent = str_replace(["\n", "\r", ' '], '', (string) $response['content']);
        $decoded = base64_decode($rawContent, strict: true);

        return new RepoFile(
            path: $path,
            content: $decoded !== false ? $decoded : '',
            sha: $response['blob_id'],
        );
    }

    public function getBranchCommitSha(DeploymentConnection $conn, string $branch): string
    {
        $encodedProject = urlencode($conn->repoCoordinate());
        $encodedBranch = rawurlencode($branch);

        $response = $this->client($conn, retry: true)
            ->get(sprintf('/projects/%s/repository/branches/%s', $encodedProject, $encodedBranch))
            ->throw()
            ->json();

        return (string) $response['commit']['id'];
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
        $encodedProject = urlencode($conn->repoCoordinate());

        $actions = array_map(fn (RepoFile $repoFile): array => [
            'action' => 'update',
            'file_path' => $repoFile->path,
            'content' => $repoFile->content,
        ], $files);

        $response = $this->client($conn)
            ->post(sprintf('/projects/%s/repository/commits', $encodedProject), [
                'branch' => $branch,
                'commit_message' => $commitMessage,
                'actions' => $actions,
            ])
            ->throw()
            ->json();

        return $response['id'];
    }

    public function createBranch(DeploymentConnection $conn, string $branchName, string $fromCommitSha): void
    {
        $encodedProject = urlencode($conn->repoCoordinate());

        $this->client($conn)
            ->post(sprintf('/projects/%s/repository/branches', $encodedProject), [
                'branch' => $branchName,
                'ref' => $fromCommitSha,
            ])
            ->throw();
    }

    public function openPullRequest(
        DeploymentConnection $conn,
        string $headBranch,
        string $title,
        string $body,
    ): PullRequestData {
        $encodedProject = urlencode($conn->repoCoordinate());

        $response = $this->client($conn)
            ->post(sprintf('/projects/%s/merge_requests', $encodedProject), [
                'source_branch' => $headBranch,
                'target_branch' => $conn->default_branch,
                'title' => $title,
                'description' => $body,
                'remove_source_branch' => true,
            ])
            ->throw()
            ->json();
        $response = $this->responseMap($response);

        return $this->pullRequestDataFromResponse($response);
    }

    public function findOpenPullRequestForBranch(DeploymentConnection $conn, string $headBranch): ?PullRequestData
    {
        $encodedProject = urlencode($conn->repoCoordinate());

        $response = $this->client($conn, retry: true)
            ->get(sprintf('/projects/%s/merge_requests', $encodedProject), [
                'state' => 'opened',
                'source_branch' => $headBranch,
                'target_branch' => $conn->default_branch,
            ])
            ->throw()
            ->json();
        $mergeRequests = $this->responseList($response);

        if (! isset($mergeRequests[0])) {
            return null;
        }

        return $this->pullRequestDataFromResponse($mergeRequests[0]);
    }

    public function enableAutoMerge(DeploymentConnection $conn, int|string $pullRequestId): void
    {
        $encodedProject = urlencode($conn->repoCoordinate());

        $this->client($conn)
            ->put(sprintf('/projects/%s/merge_requests/%s', $encodedProject, $pullRequestId), [
                'merge_when_pipeline_succeeds' => true,
            ])
            ->throw();
    }

    public function getPullRequest(DeploymentConnection $conn, int|string $pullRequestId): PullRequestData
    {
        $encodedProject = urlencode($conn->repoCoordinate());

        $response = $this->client($conn, retry: true)
            ->get(sprintf('/projects/%s/merge_requests/%s', $encodedProject, $pullRequestId))
            ->throw()
            ->json();
        $response = $this->responseMap($response);

        return $this->pullRequestDataFromResponse($response);
    }

    public function closePullRequest(DeploymentConnection $conn, int|string $pullRequestId): void
    {
        $encodedProject = urlencode($conn->repoCoordinate());

        $this->client($conn)
            ->put(sprintf('/projects/%s/merge_requests/%s', $encodedProject, $pullRequestId), [
                'state_event' => 'close',
            ])
            ->throw();
    }

    public function getDeployStatus(DeploymentConnection $conn, string $commitSha): string
    {
        $encodedProject = urlencode($conn->repoCoordinate());

        $statuses = $this->client($conn, retry: true)
            ->get(sprintf('/projects/%s/repository/commits/%s/statuses', $encodedProject, $commitSha))
            ->throw()
            ->json();
        $statuses = $this->responseList($statuses);

        foreach ($statuses as $status) {
            if (($status['status'] ?? null) === 'failed') {
                return 'failure';
            }
        }

        foreach ($statuses as $status) {
            if (in_array($status['status'] ?? null, ['running', 'pending'], true)) {
                return 'pending';
            }
        }

        return 'success';
    }

    private function client(DeploymentConnection $conn, bool $retry = false): PendingRequest
    {
        $conn = RefreshProviderTokenAction::run($conn);

        $request = $this->http
            ->baseUrl('https://gitlab.com/api/v4')
            ->withHeader('PRIVATE-TOKEN', $conn->access_token_encrypted)
            ->withHeader('Content-Type', 'application/json')
            ->timeout($this->httpTimeout())
            ->connectTimeout(5);

        if ($retry) {
            $request->retry(2, 200, throw: false);
        }

        return $request;
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
            id: $response['iid'],
            url: $response['web_url'],
            state: $response['state'],
            headBranch: $response['source_branch'],
            baseBranch: $response['target_branch'],
            headSha: $response['sha'] ?? '',
            merged: $response['state'] === 'merged',
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
