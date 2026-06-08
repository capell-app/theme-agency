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

final class BitbucketCallbackController
{
    public function __invoke(Request $request): RedirectResponse
    {
        abort_unless(DeploymentConnectionPage::canManageConnections(), 403);

        $connectionData = ConsumeOAuthStateAction::run(GitProviderType::Bitbucket, $request->query('state'));
        if ($connectionData === null) {
            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_invalid_state')]);
        }

        $code = $request->query('code');
        if (! is_string($code) || $code === '') {
            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_missing_code')]);
        }

        try {
            $tokenResponse = Http::withBasicAuth(
                $this->configString('capell-deployments.oauth.bitbucket.client_id'),
                $this->configString('capell-deployments.oauth.bitbucket.client_secret'),
            )
                ->asForm()
                ->timeout($this->httpTimeout())
                ->post('https://bitbucket.org/site/oauth2/access_token', [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => route('capell-deployments.oauth.bitbucket'),
                ])
                ->json();
            $tokenResponse = $this->responseMap($tokenResponse);
        } catch (ConnectionException $connectionException) {
            Log::warning('capell-deployments: Bitbucket OAuth token request failed', [
                'error' => $connectionException->getMessage(),
            ]);

            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_failed', ['provider' => 'Bitbucket'])]);
        }

        $accessToken = $tokenResponse['access_token'] ?? null;
        $refreshToken = $tokenResponse['refresh_token'] ?? null;
        $expiresIn = $tokenResponse['expires_in'] ?? null;
        if (! is_string($accessToken) || $accessToken === '') {
            Log::warning('capell-deployments: Bitbucket OAuth token exchange failed', $this->redactTokenResponse($tokenResponse));

            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_failed', ['provider' => 'Bitbucket'])]);
        }

        try {
            $userResponse = Http::withToken($accessToken)
                ->timeout($this->httpTimeout())
                ->get('https://api.bitbucket.org/2.0/user')
                ->json();
            $userResponse = $this->responseMap($userResponse);
        } catch (ConnectionException $connectionException) {
            Log::warning('capell-deployments: Bitbucket OAuth user request failed', [
                'error' => $connectionException->getMessage(),
            ]);

            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_user_failed', ['provider' => 'Bitbucket'])]);
        }

        $userId = $userResponse['account_id'] ?? null;
        if (! is_string($userId) || $userId === '') {
            return back()->withErrors([__('capell-deployments::plugins.deployment_connection.oauth_user_failed', ['provider' => 'Bitbucket'])]);
        }

        ConnectDeploymentAction::run(
            provider: GitProviderType::Bitbucket,
            repoOwner: $connectionData->repoOwner,
            repoName: $connectionData->repoName,
            accessToken: $accessToken,
            refreshToken: is_string($refreshToken) ? $refreshToken : null,
            installPolicy: $connectionData->installPolicy,
            expiresIn: is_numeric($expiresIn) ? (int) $expiresIn : null,
        );

        return redirect()->to(DeploymentConnectionPage::getUrl())
            ->with('status', __('capell-deployments::plugins.deployment_connection.oauth_connected', ['provider' => 'Bitbucket']));
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

    private function configString(string $key): string
    {
        $value = config($key);

        return is_string($value) ? $value : '';
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
