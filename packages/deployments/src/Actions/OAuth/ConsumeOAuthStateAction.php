<?php

declare(strict_types=1);

namespace Capell\Deployments\Actions\OAuth;

use Capell\Deployments\Data\OAuthConnectionData;
use Capell\Deployments\Enums\GitProviderType;
use Capell\Deployments\Enums\InstallPolicy;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static OAuthConnectionData|null run(GitProviderType $provider, mixed $state)
 */
final class ConsumeOAuthStateAction
{
    use AsAction;

    public function handle(GitProviderType $provider, mixed $state): ?OAuthConnectionData
    {
        if (! is_string($state) || $state === '') {
            return null;
        }

        $payload = session()->pull($this->sessionKey($provider));
        if (! is_array($payload)) {
            return null;
        }

        $expectedState = $payload['state'] ?? null;
        if (! is_string($expectedState) || $expectedState === '' || ! hash_equals($expectedState, $state)) {
            return null;
        }

        $repoOwner = $payload['repo_owner'] ?? null;
        $repoName = $payload['repo_name'] ?? null;
        if (! is_string($repoOwner) || trim($repoOwner) === '' || ! is_string($repoName) || trim($repoName) === '') {
            return null;
        }

        $installPolicyValue = $payload['install_policy'] ?? null;
        $installPolicy = (is_string($installPolicyValue) ? InstallPolicy::tryFrom($installPolicyValue) : null)
            ?? InstallPolicy::PullRequestAutoMerge;

        return new OAuthConnectionData(
            provider: $provider,
            repoOwner: trim($repoOwner),
            repoName: trim($repoName),
            installPolicy: $installPolicy,
        );
    }

    private function sessionKey(GitProviderType $provider): string
    {
        return sprintf('capell-deployments.oauth_state.%s', $provider->value);
    }
}
