<?php

declare(strict_types=1);

namespace Capell\Deployments\Services\GitProvider;

use Capell\Deployments\Contracts\GitProviderContract;
use Capell\Deployments\Data\PullRequestData;
use Capell\Deployments\Data\RepoFile;
use Capell\Deployments\Models\DeploymentConnection;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use RuntimeException;

final class GitHubProvider implements GitProviderContract
{
    public function __construct(private readonly Factory $http) {}

    public function getFile(DeploymentConnection $conn, string $path): RepoFile
    {
        $response = $this->client($conn)
            ->retry(2, 200, throw: false)
            ->get(sprintf('/repos/%s/%s/contents/%s', $conn->repo_owner, $conn->repo_name, $path))
            ->throw()
            ->json();

        $rawContent = str_replace(["\n", "\r", ' '], '', $response['content']);

        return new RepoFile(
            path: $path,
            content: base64_decode($rawContent, strict: true) !== false ? base64_decode($rawContent, strict: true) : '',
            sha: $response['sha'],
        );
    }

    public function getBranchCommitSha(DeploymentConnection $conn, string $branch): string
    {
        $branchData = $this->client($conn)
            ->retry(2, 200, throw: false)
            ->get(sprintf('/repos/%s/%s/branches/%s', $conn->repo_owner, $conn->repo_name, $branch))
            ->throw()
            ->json();

        return (string) $branchData['commit']['sha'];
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
        $client = $this->client($conn);
        $owner = $conn->repo_owner;
        $repo = $conn->repo_name;

        $branchData = $client
            ->retry(2, 200, throw: false)
            ->get(sprintf('/repos/%s/%s/branches/%s', $owner, $repo, $branch))
            ->throw()
            ->json();

        $latestSha = $branchData['commit']['sha'];
        $treeSha = $branchData['commit']['commit']['tree']['sha'];

        $treeItems = [];

        foreach ($files as $repoFile) {
            $blobResponse = $client
                ->post(sprintf('/repos/%s/%s/git/blobs', $owner, $repo), [
                    'content' => $repoFile->content,
                    'encoding' => 'utf-8',
                ])
                ->throw()
                ->json();

            $treeItems[] = [
                'path' => $repoFile->path,
                'mode' => '100644',
                'type' => 'blob',
                'sha' => $blobResponse['sha'],
            ];
        }

        $newTree = $client
            ->post(sprintf('/repos/%s/%s/git/trees', $owner, $repo), [
                'base_tree' => $treeSha,
                'tree' => $treeItems,
            ])
            ->throw()
            ->json();

        $newCommit = $client
            ->post(sprintf('/repos/%s/%s/git/commits', $owner, $repo), [
                'message' => $commitMessage,
                'tree' => $newTree['sha'],
                'parents' => [$latestSha],
            ])
            ->throw()
            ->json();

        $commitSha = $newCommit['sha'];

        $client
            ->patch(sprintf('/repos/%s/%s/git/refs/heads/%s', $owner, $repo, $branch), [
                'sha' => $commitSha,
            ])
            ->throw();

        return $commitSha;
    }

    public function createBranch(DeploymentConnection $conn, string $branchName, string $fromCommitSha): void
    {
        $this->client($conn)
            ->post(sprintf('/repos/%s/%s/git/refs', $conn->repo_owner, $conn->repo_name), [
                'ref' => 'refs/heads/' . $branchName,
                'sha' => $fromCommitSha,
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
            ->post(sprintf('/repos/%s/%s/pulls', $conn->repo_owner, $conn->repo_name), [
                'title' => $title,
                'body' => $body,
                'head' => $headBranch,
                'base' => $conn->default_branch,
            ])
            ->throw()
            ->json();

        return $this->pullRequestDataFromResponse($response);
    }

    public function enableAutoMerge(DeploymentConnection $conn, int|string $pullRequestId): void
    {
        $nodeIdQuery = <<<'GRAPHQL'
            query($owner: String!, $name: String!, $number: Int!) {
                repository(owner: $owner, name: $name) {
                    pullRequest(number: $number) {
                        id
                    }
                }
            }
            GRAPHQL;

        $nodeIdResponse = $this->graphqlClient($conn)
            ->post('/graphql', [
                'query' => $nodeIdQuery,
                'variables' => [
                    'owner' => $conn->repo_owner,
                    'name' => $conn->repo_name,
                    'number' => (int) $pullRequestId,
                ],
            ])
            ->throw()
            ->json();

        $this->assertNoGraphqlErrors($nodeIdResponse);

        $pullRequestNodeId = $nodeIdResponse['data']['repository']['pullRequest']['id'];

        $autoMergeMutation = <<<'GRAPHQL'
            mutation($id: ID!) {
                enablePullRequestAutoMerge(input: { pullRequestId: $id, mergeMethod: SQUASH }) {
                    pullRequest {
                        number
                    }
                }
            }
            GRAPHQL;

        $mutationResponse = $this->graphqlClient($conn)
            ->post('/graphql', [
                'query' => $autoMergeMutation,
                'variables' => [
                    'id' => $pullRequestNodeId,
                ],
            ])
            ->throw()
            ->json();

        $this->assertNoGraphqlErrors($mutationResponse);
    }

    public function getPullRequest(DeploymentConnection $conn, int|string $pullRequestId): PullRequestData
    {
        $response = $this->client($conn)
            ->retry(2, 200, throw: false)
            ->get(sprintf('/repos/%s/%s/pulls/%s', $conn->repo_owner, $conn->repo_name, $pullRequestId))
            ->throw()
            ->json();

        return $this->pullRequestDataFromResponse($response);
    }

    public function closePullRequest(DeploymentConnection $conn, int|string $pullRequestId): void
    {
        $this->client($conn)
            ->patch(sprintf('/repos/%s/%s/pulls/%s', $conn->repo_owner, $conn->repo_name, $pullRequestId), [
                'state' => 'closed',
            ])
            ->throw();
    }

    public function getDeployStatus(DeploymentConnection $conn, string $commitSha): string
    {
        $response = $this->client($conn)
            ->retry(2, 200, throw: false)
            ->get(sprintf('/repos/%s/%s/commits/%s/check-runs', $conn->repo_owner, $conn->repo_name, $commitSha))
            ->throw()
            ->json();

        $checkRuns = $response['check_runs'] ?? [];

        foreach ($checkRuns as $checkRun) {
            if ($checkRun['conclusion'] === 'failure') {
                return 'failure';
            }
        }

        foreach ($checkRuns as $checkRun) {
            $conclusion = $checkRun['conclusion'];
            if (in_array($conclusion, [null, 'queued', 'in_progress'], true)) {
                return 'pending';
            }

            $status = $checkRun['status'] ?? null;
            if ($status === 'in_progress' || $status === 'queued') {
                return 'pending';
            }
        }

        return 'success';
    }

    private function client(DeploymentConnection $conn): PendingRequest
    {
        return $this->http
            ->baseUrl('https://api.github.com')
            ->withToken($conn->access_token_encrypted)
            ->withHeader('Accept', 'application/vnd.github+json')
            ->withHeader('X-GitHub-Api-Version', '2022-11-28')
            ->retry(2, 200, throw: false)
            ->timeout(10)
            ->connectTimeout(5);
    }

    private function graphqlClient(DeploymentConnection $conn): PendingRequest
    {
        return $this->http
            ->baseUrl('https://api.github.com')
            ->withToken($conn->access_token_encrypted)
            ->withHeader('Accept', 'application/vnd.github+json')
            ->withHeader('X-GitHub-Api-Version', '2022-11-28')
            ->retry(2, 200, throw: false)
            ->timeout(10)
            ->connectTimeout(5);
    }

    /**
     * @param  array<string, mixed>  $json
     */
    private function assertNoGraphqlErrors(array $json): void
    {
        if (isset($json['errors']) && is_array($json['errors']) && $json['errors'] !== []) {
            $messages = array_map(
                static fn (array $error): string => (string) ($error['message'] ?? 'unknown error'),
                $json['errors'],
            );

            throw new RuntimeException('GitHub GraphQL error: ' . implode('; ', $messages));
        }
    }

    /** @param array<string, mixed> $response */
    private function pullRequestDataFromResponse(array $response): PullRequestData
    {
        return new PullRequestData(
            id: $response['number'],
            url: $response['html_url'],
            state: $response['state'],
            headBranch: $response['head']['ref'],
            baseBranch: $response['base']['ref'],
            headSha: $response['head']['sha'],
            merged: (bool) $response['merged'],
        );
    }
}
