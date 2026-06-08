<?php

declare(strict_types=1);

namespace Capell\Deployments\Actions;

use Capell\Deployments\Enums\GitProviderType;
use Capell\Deployments\Models\DeploymentConnection;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static DeploymentConnection run(DeploymentConnection $connection)
 */
final class RefreshProviderTokenAction
{
    use AsAction;

    public function handle(DeploymentConnection $connection): DeploymentConnection
    {
        if (! $this->shouldRefresh($connection)) {
            return $connection;
        }

        return match ($connection->provider) {
            GitProviderType::GitLab => $this->refreshGitLab($connection),
            GitProviderType::Bitbucket => $this->refreshBitbucket($connection),
            GitProviderType::GitHub => $connection,
        };
    }

    private function shouldRefresh(DeploymentConnection $connection): bool
    {
        if (! is_string($connection->refresh_token_encrypted) || $connection->refresh_token_encrypted === '') {
            return false;
        }

        if (! $connection->token_expires_at instanceof DateTimeInterface) {
            return false;
        }

        return CarbonImmutable::instance($connection->token_expires_at)->subMinute()->isPast();
    }

    private function refreshGitLab(DeploymentConnection $connection): DeploymentConnection
    {
        try {
            $tokenResponse = Http::timeout($this->httpTimeout())
                ->post('https://gitlab.com/oauth/token', [
                    'client_id' => config('capell-deployments.oauth.gitlab.client_id'),
                    'client_secret' => config('capell-deployments.oauth.gitlab.client_secret'),
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $connection->refresh_token_encrypted,
                ])
                ->throw()
                ->json();
            $tokenResponse = $this->responseMap($tokenResponse);
        } catch (ConnectionException|RequestException $connectionException) {
            Log::warning('capell-deployments: GitLab OAuth token refresh request failed', [
                'connection_id' => $connection->getKey(),
                'error' => $connectionException->getMessage(),
            ]);

            return $connection;
        }

        return $this->persistTokenResponse($connection, $tokenResponse);
    }

    private function refreshBitbucket(DeploymentConnection $connection): DeploymentConnection
    {
        try {
            $tokenResponse = Http::withBasicAuth(
                $this->configString('capell-deployments.oauth.bitbucket.client_id'),
                $this->configString('capell-deployments.oauth.bitbucket.client_secret'),
            )
                ->asForm()
                ->timeout($this->httpTimeout())
                ->post('https://bitbucket.org/site/oauth2/access_token', [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $connection->refresh_token_encrypted,
                ])
                ->throw()
                ->json();
            $tokenResponse = $this->responseMap($tokenResponse);
        } catch (ConnectionException|RequestException $connectionException) {
            Log::warning('capell-deployments: Bitbucket OAuth token refresh request failed', [
                'connection_id' => $connection->getKey(),
                'error' => $connectionException->getMessage(),
            ]);

            return $connection;
        }

        return $this->persistTokenResponse($connection, $tokenResponse);
    }

    /**
     * @param  array<string, mixed>  $tokenResponse
     */
    private function persistTokenResponse(DeploymentConnection $connection, array $tokenResponse): DeploymentConnection
    {
        $accessToken = $tokenResponse['access_token'] ?? null;

        if (! is_string($accessToken) || $accessToken === '') {
            return $connection;
        }

        $refreshToken = $tokenResponse['refresh_token'] ?? null;
        $expiresIn = $tokenResponse['expires_in'] ?? null;

        $connection->forceFill([
            'access_token_encrypted' => $accessToken,
            'refresh_token_encrypted' => is_string($refreshToken) && $refreshToken !== ''
                ? $refreshToken
                : $connection->refresh_token_encrypted,
            'token_expires_at' => is_numeric($expiresIn)
                ? CarbonImmutable::now()->addSeconds(max(1, (int) $expiresIn))
                : null,
        ])->save();

        return $connection->refresh();
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
