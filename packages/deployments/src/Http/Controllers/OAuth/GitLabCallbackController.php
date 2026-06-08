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

final class GitLabCallbackController
{
    public function __invoke(Request $request): RedirectResponse
    {
        abort_unless(DeploymentConnectionPage::canManageConnections(), 403);

        $connectionData = ConsumeOAuthStateAction::run(GitProviderType::GitLab, $request->query('state'));
        if ($connectionData === null) {
            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_invalid_state')]);
        }

        $code = $request->query('code');
        if (! is_string($code) || $code === '') {
            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_missing_code')]);
        }

        try {
            $tokenResponse = Http::timeout($this->httpTimeout())
                ->post('https://gitlab.com/oauth/token', [
                    'client_id' => config('capell-deployments.oauth.gitlab.client_id'),
                    'client_secret' => config('capell-deployments.oauth.gitlab.client_secret'),
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => route('capell-deployments.oauth.gitlab'),
                ])
                ->json();
            $tokenResponse = $this->responseMap($tokenResponse);
        } catch (ConnectionException $connectionException) {
            Log::warning('capell-deployments: GitLab OAuth token request failed', [
                'error' => $connectionException->getMessage(),
            ]);

            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_failed', ['provider' => 'GitLab'])]);
        }

        $accessToken = $tokenResponse['access_token'] ?? null;
        $refreshToken = $tokenResponse['refresh_token'] ?? null;
        $expiresIn = $tokenResponse['expires_in'] ?? null;
        if (! is_string($accessToken) || $accessToken === '') {
            Log::warning('capell-deployments: GitLab OAuth token exchange failed', $this->redactTokenResponse($tokenResponse));

            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_failed', ['provider' => 'GitLab'])]);
        }

        try {
            $userResponse = Http::withHeader('PRIVATE-TOKEN', $accessToken)
                ->timeout($this->httpTimeout())
                ->get('https://gitlab.com/api/v4/user')
                ->json();
            $userResponse = $this->responseMap($userResponse);
        } catch (ConnectionException $connectionException) {
            Log::warning('capell-deployments: GitLab OAuth user request failed', [
                'error' => $connectionException->getMessage(),
            ]);

            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_user_failed', ['provider' => 'GitLab'])]);
        }

        $userId = $userResponse['id'] ?? null;
        if (! is_int($userId) && ! is_string($userId)) {
            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_user_failed', ['provider' => 'GitLab'])]);
        }

        ConnectDeploymentAction::run(
            provider: GitProviderType::GitLab,
            repoOwner: $connectionData->repoOwner,
            repoName: $connectionData->repoName,
            accessToken: $accessToken,
            refreshToken: is_string($refreshToken) ? $refreshToken : null,
            installPolicy: $connectionData->installPolicy,
            expiresIn: is_numeric($expiresIn) ? (int) $expiresIn : null,
        );

        return redirect()->to(DeploymentConnectionPage::getUrl())
            ->with('status', __('capell-deployments::plugins.deployment_connection.oauth_connected', ['provider' => 'GitLab']));
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
        $timeout = config('capell-deployments.http_timeout', 10);

        if (is_int($timeout)) {
            return max(1, $timeout);
        }

        return is_string($timeout) && ctype_digit($timeout) ? max(1, (int) $timeout) : 10;
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
}
