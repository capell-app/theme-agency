<?php

declare(strict_types=1);

namespace Capell\Deployments\Actions\OAuth;

use Capell\Deployments\Enums\GitProviderType;
use Capell\Deployments\Enums\InstallPolicy;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class CreateOAuthStateAction
{
    use AsAction;

    public function handle(
        GitProviderType $provider,
        ?string $repoOwner = null,
        ?string $repoName = null,
        InstallPolicy $installPolicy = InstallPolicy::PullRequestAutoMerge,
    ): string
    {
        $state = Str::random(40);

        session()->put($this->sessionKey($provider), [
            'state' => $state,
            'repo_owner' => $repoOwner === null ? null : trim($repoOwner),
            'repo_name' => $repoName === null ? null : trim($repoName),
            'install_policy' => $installPolicy->value,
        ]);

        return $state;
    }

    private function sessionKey(GitProviderType $provider): string
    {
        return sprintf('capell-deployments.oauth_state.%s', $provider->value);
    }
}
