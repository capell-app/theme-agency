<?php

declare(strict_types=1);

namespace Capell\Deployments\Http\Controllers\OAuth;

use Capell\Deployments\Actions\ConnectDeploymentAction;
use Capell\Deployments\Actions\OAuth\ConsumeOAuthStateAction;
use Capell\Deployments\Enums\GitProviderType;
use Capell\Deployments\Filament\Pages\DeploymentConnectionPage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class GitHubCallbackController
{
    public function __invoke(Request $request): RedirectResponse
    {
        abort_unless(DeploymentConnectionPage::canManageConnections(), 403);

        $connectionData = ConsumeOAuthStateAction::run(GitProviderType::GitHub, $request->query('state'));
        if ($connectionData === null) {
            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_invalid_state')]);
        }

        $code = $request->query('code');
        if (! is_string($code) || $code === '') {
            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_missing_code')]);
        }

        try {
            $tokenResponse = Http::withHeaders(['Accept' => 'application/json'])
                ->timeout($this->httpTimeout())
                ->post('https://github.com/login/oauth/access_token', [
                    'client_id' => config('capell-deployments.oauth.github.client_id'),
                    'client_secret' => config('capell-deployments.oauth.github.client_secret'),
                    'code' => $code,
                ])
                ->json();
        } catch (ConnectionException $connectionException) {
            Log::warning('capell-deployments: GitHub OAuth token request failed', [
                'error' => $connectionException->getMessage(),
            ]);

            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_failed', ['provider' => 'GitHub'])]);
        }

        $accessToken = $tokenResponse['access_token'] ?? null;
        if (! is_string($accessToken) || $accessToken === '') {
            Log::warning('capell-deployments: GitHub OAuth token exchange failed', $this->redactTokenResponse($tokenResponse));

            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_failed', ['provider' => 'GitHub'])]);
        }

        try {
            $userResponse = Http::withToken($accessToken)
                ->withHeader('Accept', 'application/vnd.github+json')
                ->timeout($this->httpTimeout())
                ->get('https://api.github.com/user')
                ->json();
        } catch (ConnectionException $connectionException) {
            Log::warning('capell-deployments: GitHub OAuth user request failed', [
                'error' => $connectionException->getMessage(),
            ]);

            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_user_failed', ['provider' => 'GitHub'])]);
        }

        $userId = $userResponse['id'] ?? null;
        if (! is_int($userId) && ! is_string($userId)) {
            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_user_failed', ['provider' => 'GitHub'])]);
        }

        ConnectDeploymentAction::run(
            provider: GitProviderType::GitHub,
            repoOwner: $connectionData->repoOwner,
            repoName: $connectionData->repoName,
            accessToken: $accessToken,
            installPolicy: $connectionData->installPolicy,
        );

        return redirect()->to(DeploymentConnectionPage::getUrl())
            ->with('status', __('capell-deployments::plugins.deployment_connection.oauth_connected', ['provider' => 'GitHub']));
    }

    /**
     * Redact OAuth provider response so secrets never reach the log channel.
     *
     * @return array<string, scalar|null>
     */
    private function redactTokenResponse(mixed $tokenResponse): array
    {
        if (! is_array($tokenResponse)) {
            return ['response_type' => gettype($tokenResponse)];
        }

        $safeKeys = ['error', 'error_description', 'error_uri', 'status', 'message'];
        $redacted = [];
        foreach ($safeKeys as $safeKey) {
            if (array_key_exists($safeKey, $tokenResponse) && is_scalar($tokenResponse[$safeKey])) {
                $redacted[$safeKey] = $tokenResponse[$safeKey];
            }
        }

        return $redacted;
    }

    private function httpTimeout(): int
    {
        return max(1, (int) config('capell-deployments.http_timeout', 10));
    }
}
