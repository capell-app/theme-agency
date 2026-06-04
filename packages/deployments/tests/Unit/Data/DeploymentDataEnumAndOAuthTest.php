<?php

declare(strict_types=1);

use Capell\Deployments\Actions\OAuth\ConsumeOAuthStateAction;
use Capell\Deployments\Actions\OAuth\CreateOAuthStateAction;
use Capell\Deployments\Actions\OAuth\ValidateOAuthStateAction;
use Capell\Deployments\Data\ComposerRequirementData;
use Capell\Deployments\Data\PublishComposerChangeResultData;
use Capell\Deployments\Data\PullRequestData;
use Capell\Deployments\Data\RepoFile;
use Capell\Deployments\Enums\GitProviderType;
use Capell\Deployments\Enums\InstallPolicy;
use Capell\Deployments\Health\DeploymentsHealthCheck;

it('maps deployment data objects', function (): void {
    $requirement = new ComposerRequirementData(
        composerName: 'capell/example-package',
        versionConstraint: '^1.2',
        repositoryUrl: 'https://github.com/capell/example-package',
        label: 'Example Package',
    );
    $repoFile = new RepoFile(
        path: 'composer.json',
        content: '{"require":{}}',
        sha: 'abc123',
    );
    $pullRequest = new PullRequestData(
        id: '42',
        url: 'https://github.com/capell/app/pull/42',
        state: 'open',
        headBranch: 'capell-deployments/add-example',
        baseBranch: '4.x',
        headSha: 'abc123',
        merged: false,
    );
    $result = new PublishComposerChangeResultData(
        provider: GitProviderType::GitHub,
        pullRequestUrl: 'https://github.com/capell/app/pull/42',
        commitSha: 'abc123',
        pullRequestId: 42,
    );

    expect($requirement->composerName)->toBe('capell/example-package')
        ->and($requirement->versionConstraint)->toBe('^1.2')
        ->and($requirement->repositoryUrl)->toBe('https://github.com/capell/example-package')
        ->and($repoFile->toArray())->toBe([
            'path' => 'composer.json',
            'content' => '{"require":{}}',
            'sha' => 'abc123',
        ])
        ->and($pullRequest->headBranch)->toBe('capell-deployments/add-example')
        ->and($pullRequest->baseBranch)->toBe('4.x')
        ->and($result->provider)->toBe(GitProviderType::GitHub)
        ->and($result->pullRequestId)->toBe(42);
});

it('creates one-time OAuth states per provider', function (): void {
    $state = CreateOAuthStateAction::run(GitProviderType::GitHub);

    expect($state)->toBeString()
        ->and(strlen((string) $state))->toBe(40)
        ->and(ValidateOAuthStateAction::run(GitProviderType::GitHub, $state))->toBeTrue()
        ->and(ValidateOAuthStateAction::run(GitProviderType::GitHub, $state))->toBeFalse();
});

it('consumes OAuth states with repository coordinates', function (): void {
    $state = CreateOAuthStateAction::run(GitProviderType::GitLab, ' capell ', ' app ');

    $connectionData = ConsumeOAuthStateAction::run(GitProviderType::GitLab, $state);

    expect($connectionData)->not->toBeNull()
        ->and($connectionData?->provider)->toBe(GitProviderType::GitLab)
        ->and($connectionData?->repoOwner)->toBe('capell')
        ->and($connectionData?->repoName)->toBe('app')
        ->and(ConsumeOAuthStateAction::run(GitProviderType::GitLab, $state))->toBeNull();
});

it('rejects OAuth states without repository coordinates when consumed for a connection', function (): void {
    $state = CreateOAuthStateAction::run(GitProviderType::GitHub);

    expect(ConsumeOAuthStateAction::run(GitProviderType::GitHub, $state))->toBeNull();
});

it('rejects missing mismatched or wrong-provider OAuth states', function (): void {
    $state = CreateOAuthStateAction::run(GitProviderType::GitLab);

    expect(ValidateOAuthStateAction::run(GitProviderType::GitLab, null))->toBeFalse()
        ->and(ValidateOAuthStateAction::run(GitProviderType::GitLab, ''))->toBeFalse()
        ->and(ValidateOAuthStateAction::run(GitProviderType::GitHub, $state))->toBeFalse()
        ->and(ValidateOAuthStateAction::run(GitProviderType::GitLab, 'wrong-state'))->toBeFalse();
});

it('defines deployment enum labels and health metadata', function (): void {
    expect(DeploymentsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and(GitProviderType::GitHub->getLabel())->toBe('GitHub')
        ->and(GitProviderType::GitLab->getLabel())->toBe('GitLab')
        ->and(GitProviderType::Bitbucket->getLabel())->toBe('Bitbucket')
        ->and(InstallPolicy::DirectCommit->getLabel())->toBe('Direct commit (fastest)')
        ->and(InstallPolicy::PullRequestAutoMerge->getLabel())->toBe('Pull request, auto-merge on green CI (recommended)')
        ->and(InstallPolicy::PullRequestManual->getLabel())->toBe('Pull request, manual review (most cautious)');
});
