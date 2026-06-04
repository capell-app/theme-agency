<?php

declare(strict_types=1);

use Capell\Deployments\Actions\PrepareComposerRequirementCommitAction;
use Capell\Deployments\Actions\PublishComposerRequirementAction;
use Capell\Deployments\Contracts\PublishesComposerChanges;
use Capell\Deployments\Data\ComposerRequirementData;
use Capell\Deployments\Data\RepoFile;
use Capell\Deployments\Enums\InstallPolicy;
use Capell\Deployments\Models\DeploymentConnection;
use Capell\Deployments\Services\GitProvider\GitHubProvider;
use Capell\Deployments\Tests\Fixtures\Autoload\FakeComposerPublisher;

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
        ->and($result->commitSha)->toBeNull()
        ->and($provider->branches)->toHaveCount(1)
        ->and($provider->branches[0]['from'])->toBe('branch-commit-sha')
        ->and($provider->commits)->toHaveCount(1)
        ->and($provider->commits[0]['branch'])->toStartWith('capell/add-extension-pr-extension-')
        ->and($provider->autoMergedPullRequestIds)->toBe([123]);
});

it('bound composer publisher fails loudly when multiple active connections exist', function (): void {
    DeploymentConnection::factory()->github()->create();
    DeploymentConnection::factory()->gitlab()->create();

    expect(fn (): mixed => app(PublishesComposerChanges::class)->publish(
        new ComposerRequirementData(composerName: 'capell/ambiguous-extension'),
    ))->toThrow(LogicException::class, 'multiple active deployment connections exist');
});
