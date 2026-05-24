<?php

declare(strict_types=1);

use Capell\Deployments\Actions\OAuth\CreateOAuthStateAction;
use Capell\Deployments\Actions\OAuth\ValidateOAuthStateAction;
use Capell\Deployments\Casts\EncryptedString;
use Capell\Deployments\Data\ComposerRequirementData;
use Capell\Deployments\Data\PublishComposerChangeResultData;
use Capell\Deployments\Data\PullRequestData;
use Capell\Deployments\Data\RepoFile;
use Capell\Deployments\Enums\GitProviderType;
use Capell\Deployments\Enums\InstallPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

it('keeps deployment git payloads as typed data', function (): void {
    $requirement = new ComposerRequirementData('capell-app/search', '^4.0', 'https://example.test/repo.git', 'Search');
    $result = new PublishComposerChangeResultData(GitProviderType::GitHub, 'https://github.test/pr/1', 'abc123', 1);
    $pullRequest = new PullRequestData(1, 'https://github.test/pr/1', 'open', 'feature/search', '4.x', 'abc123', false);
    $repoFile = new RepoFile('composer.json', '{}', 'sha-1');

    expect($requirement->versionConstraint)->toBe('^4.0')
        ->and($result->provider)->toBe(GitProviderType::GitHub)
        ->and($pullRequest->headBranch)->toBe('feature/search')
        ->and($repoFile->sha)->toBe('sha-1');
});

it('labels deployment enum choices', function (): void {
    expect(GitProviderType::GitHub->getLabel())->toBe('GitHub')
        ->and(GitProviderType::GitLab->getLabel())->toBe('GitLab')
        ->and(GitProviderType::Bitbucket->getLabel())->toBe('Bitbucket')
        ->and(InstallPolicy::DirectCommit->getLabel())->toBe('Direct commit (fastest)')
        ->and(InstallPolicy::PullRequestAutoMerge->getLabel())->toContain('auto-merge')
        ->and(InstallPolicy::PullRequestManual->getLabel())->toContain('manual review');
});

it('encrypts and decrypts nullable deployment strings', function (): void {
    $cast = new EncryptedString;
    $model = new class extends Model
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;
    };

    $encrypted = $cast->set($model, 'token', 'secret-token', []);

    expect($encrypted)->not->toBe('secret-token')
        ->and($cast->get($model, 'token', $encrypted, []))->toBe('secret-token')
        ->and($cast->set($model, 'token', null, []))->toBeNull()
        ->and($cast->get($model, 'token', null, []))->toBeNull();
});

it('creates and consumes provider oauth state once', function (): void {
    $state = CreateOAuthStateAction::run(GitProviderType::GitHub);

    expect($state)->toHaveLength(40)
        ->and(ValidateOAuthStateAction::run(GitProviderType::GitHub, $state))->toBeTrue()
        ->and(ValidateOAuthStateAction::run(GitProviderType::GitHub, $state))->toBeFalse()
        ->and(ValidateOAuthStateAction::run(GitProviderType::GitLab, ''))->toBeFalse();
});
