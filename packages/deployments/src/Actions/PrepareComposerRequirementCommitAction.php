<?php

declare(strict_types=1);

namespace Capell\Deployments\Actions;

use Capell\Deployments\Data\ComposerRequirementData;
use Capell\Deployments\Data\RepoFile;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;

final class PrepareComposerRequirementCommitAction
{
    use AsAction;

    public function handle(ComposerRequirementData $requirement, RepoFile $composerJson): RepoFile
    {
        $composer = json_decode($composerJson->content, associative: true, flags: JSON_THROW_ON_ERROR);

        throw_if(
            $requirement->composerName === '',
            InvalidArgumentException::class,
            'Composer package name is required.',
        );

        if ($requirement->repositoryUrl !== null) {
            $composer['repositories'] ??= [];
            $repositories = is_array($composer['repositories']) ? $composer['repositories'] : [];
            $alreadyPresent = false;

            foreach ($repositories as $repository) {
                if (is_array($repository) && ($repository['url'] ?? null) === $requirement->repositoryUrl) {
                    $alreadyPresent = true;
                    break;
                }
            }

            if (! $alreadyPresent) {
                $composer['repositories'][] = ['type' => 'vcs', 'url' => $requirement->repositoryUrl];
            }
        }

        $composer['require'][$requirement->composerName] = $requirement->versionConstraint;

        return new RepoFile(
            path: 'composer.json',
            content: json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n",
            sha: $composerJson->sha,
        );
    }
}
