<?php

declare(strict_types=1);

use Capell\Deployments\Actions\CancelDeploymentPublicationAction;
use Capell\Deployments\Actions\PrepareComposerRequirementCommitAction;
use Capell\Deployments\Actions\PublishComposerRequirementAction;
use Capell\Deployments\Contracts\GitProviderContract;
use Capell\Deployments\Contracts\PublishesComposerChanges;
use Capell\Deployments\Data\ComposerRequirementData;
use Capell\Deployments\Data\PublishComposerChangeResultData;
use Capell\Deployments\Data\PullRequestData;
use Capell\Deployments\Data\RepoFile;
use Capell\Deployments\Enums\InstallPolicy;
use Capell\Deployments\Events\DeploymentPublishFailed;
use Capell\Deployments\Events\DeploymentPublishSucceeded;
use Capell\Deployments\Models\DeploymentConnection;
use Capell\Deployments\Models\DeploymentPublication;
use Capell\Deployments\Services\GitProvider\GitHubProvider;
use Capell\Deployments\Support\DeploymentPublishHookRegistry;
use Capell\Deployments\Tests\Fixtures\Autoload\FakeComposerPublisher;
use Illuminate\Support\Facades\Event;

it('prepares composer requirement commits with package requirements and missing vcs repositories', function (): void {
    $patched = PrepareComposerRequirementCommitAction::run(
        new ComposerRequirementData(
            composerName: 'capell/example-extension',
            versionConstraint: '^1.2',
            repositoryUrl: 'git@github.com:capell/example-extension.git',
        ),
        new RepoFile(
            path: 'composer.json',
            content: '{"require":{"php":"^8.3"}}',
            sha: 'base-sha',
        ),
    );

    $composer = json_decode((string) $patched->content, associative: true, flags: JSON_THROW_ON_ERROR);

    expect($patched->path)->toBe('composer.json')
        ->and($patched->sha)->toBe('base-sha')
        ->and($composer['require']['capell/example-extension'])->toBe('^1.2')
        ->and($composer['repositories'])->toContain([
            'type' => 'vcs',
            'url' => 'git@github.com:capell/example-extension.git',
        ]);
});

it('publishes composer requirements directly when the connection uses direct commits', function (): void {
    $provider = new FakeComposerPublisher;
    app()->instance(GitHubProvider::class, $provider);
    $connection = DeploymentConnection::factory()->github()->create([
        'install_policy' => InstallPolicy::DirectCommit,
        'default_branch' => '4.x',
    ]);

    $result = PublishComposerRequirementAction::run(
        new ComposerRequirementData(
            composerName: 'capell/direct-extension',
            versionConstraint: '^2.0',
        ),
        $connection,
    );

    expect($result->commitSha)->toBe('commit-sha')
        ->and($result->pullRequestUrl)->toBeNull()
        ->and($provider->commits)->toHaveCount(1)
        ->and($provider->commits[0]['branch'])->toBe('4.x')
        ->and($provider->commits[0]['message'])->toBe('Add extension capell/direct-extension');
});

it('emits a deployment publish succeeded event after recording a publish result', function (): void {
    Event::fake([DeploymentPublishSucceeded::class, DeploymentPublishFailed::class]);

    $provider = new FakeComposerPublisher;
    app()->instance(GitHubProvider::class, $provider);
    $connection = DeploymentConnection::factory()->github()->create([
        'install_policy' => InstallPolicy::DirectCommit,
        'default_branch' => '4.x',
    ]);
    $requirement = new ComposerRequirementData(
        composerName: 'capell/evented-extension',
        versionConstraint: '^2.0',
    );

    $result = PublishComposerRequirementAction::run($requirement, $connection);

    Event::assertDispatched(
        DeploymentPublishSucceeded::class,
        static fn (DeploymentPublishSucceeded $event): bool => $event->connection->is($connection)
            && $event->requirement === $requirement
            && $event->result === $result
            && $event->status === 'success',
    );
    Event::assertNotDispatched(DeploymentPublishFailed::class);
});

it('runs deployment publish hooks around successful publishes', function (): void {
    $provider = new FakeComposerPublisher;
    app()->instance(GitHubProvider::class, $provider);
    $connection = DeploymentConnection::factory()->github()->create([
        'install_policy' => InstallPolicy::DirectCommit,
        'default_branch' => '4.x',
    ]);
    $requirement = new ComposerRequirementData(
        composerName: 'capell/hooked-extension',
        versionConstraint: '^2.0',
    );
    $calls = [];

    $hooks = app(DeploymentPublishHookRegistry::class);
    $hooks->beforePublish(function (DeploymentConnection $hookConnection, ComposerRequirementData $hookRequirement) use (&$calls, $connection, $requirement): void {
        $calls[] = 'before:' . $hookRequirement->composerName;

        expect($hookConnection->is($connection))->toBeTrue()
            ->and($hookRequirement)->toBe($requirement);
    });
    $hooks->afterPublish(function (
        DeploymentConnection $hookConnection,
        ComposerRequirementData $hookRequirement,
        PublishComposerChangeResultData $result,
        ?string $status,
    ) use (&$calls, $connection, $requirement): void {
        $calls[] = 'after:' . $result->commitSha . ':' . $status;

        expect($hookConnection->is($connection))->toBeTrue()
            ->and($hookRequirement)->toBe($requirement);
    });

    PublishComposerRequirementAction::run($requirement, $connection);

    expect($calls)->toBe(['before:capell/hooked-extension', 'after:commit-sha:success']);
});

it('emits a deployment publish failed event before rethrowing provider failures', function (): void {
    Event::fake([DeploymentPublishSucceeded::class, DeploymentPublishFailed::class]);

    $provider = new class implements GitProviderContract
    {
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
            return 'branch-commit-sha';
        }

        public function commitFiles(DeploymentConnection $conn, string $branch, string $commitMessage, array $files): string
        {
            throw new RuntimeException('Provider refused the commit.');
        }

        public function createBranch(DeploymentConnection $conn, string $branchName, string $fromCommitSha): void {}

        public function openPullRequest(DeploymentConnection $conn, string $headBranch, string $title, string $body): PullRequestData
        {
            throw new RuntimeException('Provider refused the pull request.');
        }

        public function findOpenPullRequestForBranch(DeploymentConnection $conn, string $headBranch): ?PullRequestData
        {
            return null;
        }

        public function enableAutoMerge(DeploymentConnection $conn, int|string $pullRequestId): void {}

        public function getPullRequest(DeploymentConnection $conn, int|string $pullRequestId): PullRequestData
        {
            throw new RuntimeException('Provider refused the pull request lookup.');
        }

        public function closePullRequest(DeploymentConnection $conn, int|string $pullRequestId): void {}

        public function getDeployStatus(DeploymentConnection $conn, string $commitSha): string
        {
            return 'pending';
        }
    };

    app()->instance(GitHubProvider::class, $provider);
    $connection = DeploymentConnection::factory()->github()->create([
        'install_policy' => InstallPolicy::DirectCommit,
        'default_branch' => '4.x',
    ]);
    $requirement = new ComposerRequirementData(
        composerName: 'capell/failing-extension',
        versionConstraint: '^2.0',
    );
    $calls = [];

    app(DeploymentPublishHookRegistry::class)->publishFailed(function (
        DeploymentConnection $hookConnection,
        ComposerRequirementData $hookRequirement,
        Throwable $exception,
    ) use (&$calls, $connection, $requirement): void {
        $calls[] = 'failed:' . $exception->getMessage();

        expect($hookConnection->is($connection))->toBeTrue()
            ->and($hookRequirement)->toBe($requirement);
    });

    expect(fn (): PublishComposerChangeResultData => PublishComposerRequirementAction::run($requirement, $connection))
        ->toThrow(RuntimeException::class, 'Provider refused the commit.');

    Event::assertDispatched(
        DeploymentPublishFailed::class,
        static fn (DeploymentPublishFailed $event): bool => $event->connection->is($connection)
            && $event->requirement === $requirement
            && $event->exceptionClass === RuntimeException::class
            && $event->message === 'Provider refused the commit.',
    );
    Event::assertNotDispatched(DeploymentPublishSucceeded::class);
    expect($calls)->toBe(['failed:Provider refused the commit.']);
});

it('dry runs direct composer requirement publishes without committing to the default branch', function (): void {
    $provider = new FakeComposerPublisher;
    app()->instance(GitHubProvider::class, $provider);
    $connection = DeploymentConnection::factory()->github()->create([
        'install_policy' => InstallPolicy::DirectCommit,
        'default_branch' => '4.x',
    ]);

    $result = PublishComposerRequirementAction::run(
        new ComposerRequirementData(
            composerName: 'capell/direct-extension',
            versionConstraint: '^2.0',
        ),
        $connection,
        dryRun: true,
    );

    expect($result->dryRun)->toBeTrue()
        ->and($result->commitSha)->toBeNull()
        ->and($result->pullRequestUrl)->toBeNull()
        ->and($provider->commits)->toBeEmpty();
});

it('publishes composer requirements through pull requests and enables automerge when configured', function (): void {
    $provider = new FakeComposerPublisher;
    app()->instance(GitHubProvider::class, $provider);
    $connection = DeploymentConnection::factory()->github()->create([
        'install_policy' => InstallPolicy::PullRequestAutoMerge,
    ]);

    $result = PublishComposerRequirementAction::run(
        new ComposerRequirementData(
            composerName: 'capell/pr-extension',
            versionConstraint: '^3.0',
            repositoryUrl: 'git@github.com:capell/pr-extension.git',
            label: 'PR Extension',
        ),
        $connection,
    );

    expect($result->pullRequestUrl)->toBe('https://github.test/pull/123')
        ->and($result->pullRequestId)->toBe(123)
        ->and($result->commitSha)->toBe('commit-sha')
        ->and($provider->branches)->toHaveCount(1)
        ->and($provider->branches[0]['from'])->toBe('branch-commit-sha')
        ->and($provider->commits)->toHaveCount(1)
        ->and($provider->commits[0]['branch'])->toBe('capell/add-extension-pr-extension')
        ->and($provider->deployStatusCommitShas)->toBe(['commit-sha'])
        ->and($provider->autoMergedPullRequestIds)->toBe([123]);
});

it('leaves auto merge disabled when pull request health gates are not passing', function (): void {
    $provider = new FakeComposerPublisher;
    $provider->deployStatus = 'pending';

    app()->instance(GitHubProvider::class, $provider);
    $connection = DeploymentConnection::factory()->github()->create([
        'install_policy' => InstallPolicy::PullRequestAutoMerge,
    ]);

    $result = PublishComposerRequirementAction::run(
        new ComposerRequirementData(
            composerName: 'capell/pr-extension',
            versionConstraint: '^3.0',
            label: 'PR Extension',
        ),
        $connection,
    );

    $publication = DeploymentPublication::query()->where('composer_package', 'capell/pr-extension')->firstOrFail();

    expect($result->commitSha)->toBe('commit-sha')
        ->and($publication->status)->toBe('pending')
        ->and($provider->deployStatusCommitShas)->toBe(['commit-sha'])
        ->and($provider->autoMergedPullRequestIds)->toBeEmpty();
});

it('reuses an open composer requirement pull request for the same package branch', function (): void {
    $provider = new FakeComposerPublisher;
    $provider->existingPullRequest = new PullRequestData(
        id: 456,
        url: 'https://github.test/pull/456',
        state: 'open',
        headBranch: 'capell/add-extension-pr-extension',
        baseBranch: 'main',
        headSha: 'existing-sha',
        merged: false,
    );

    app()->instance(GitHubProvider::class, $provider);
    $connection = DeploymentConnection::factory()->github()->create([
        'install_policy' => InstallPolicy::PullRequestAutoMerge,
    ]);

    $result = PublishComposerRequirementAction::run(
        new ComposerRequirementData(
            composerName: 'capell/pr-extension',
            versionConstraint: '^3.0',
            label: 'PR Extension',
        ),
        $connection,
    );

    expect($result->pullRequestUrl)->toBe('https://github.test/pull/456')
        ->and($result->pullRequestId)->toBe(456)
        ->and($result->commitSha)->toBe('existing-sha')
        ->and($result->branchName)->toBe('capell/add-extension-pr-extension')
        ->and($provider->branches)->toBeEmpty()
        ->and($provider->commits)->toBeEmpty()
        ->and($provider->deployStatusCommitShas)->toBe(['existing-sha'])
        ->and($provider->autoMergedPullRequestIds)->toBe([456]);
});

it('cancels a pending pull-request publication through the provider', function (): void {
    $provider = new FakeComposerPublisher;
    app()->instance(GitHubProvider::class, $provider);
    $connection = DeploymentConnection::factory()->github()->create();
    $publication = DeploymentPublication::query()->create([
        'deployment_connection_id' => $connection->id,
        'provider' => $connection->provider,
        'repo_owner' => $connection->repo_owner,
        'repo_name' => $connection->repo_name,
        'composer_package' => 'capell/cancel-me',
        'constraint' => '^1.0',
        'branch_name' => 'capell/add-extension-cancel-me',
        'commit_sha' => 'commit-sha',
        'pull_request_id' => 789,
        'pull_request_url' => 'https://github.test/pull/789',
        'status' => 'pending',
        'dry_run' => false,
        'status_checked_at' => null,
    ]);

    $cancelled = CancelDeploymentPublicationAction::run($publication, $connection);

    expect($cancelled->status)->toBe('cancelled')
        ->and($cancelled->status_checked_at)->not->toBeNull()
        ->and($provider->closedPullRequestIds)->toBe([789]);
});

it('bound composer publisher fails loudly when multiple active connections exist', function (): void {
    DeploymentConnection::factory()->github()->create();
    DeploymentConnection::factory()->gitlab()->create();

    expect(fn (): mixed => resolve(PublishesComposerChanges::class)->publish(
        new ComposerRequirementData(composerName: 'capell/ambiguous-extension'),
    ))->toThrow(LogicException::class, 'multiple active deployment connections exist');
});
