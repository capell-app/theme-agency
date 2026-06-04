<?php

declare(strict_types=1);

namespace Capell\Deployments\Filament\Pages;

use BackedEnum;
use Capell\Deployments\Actions\OAuth\CreateOAuthStateAction;
use Capell\Deployments\Enums\GitProviderType;
use Capell\Deployments\Models\DeploymentConnection;
use Closure;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Override;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class DeploymentConnectionPage extends Page
{
    public string $repoOwner = '';

    public string $repoName = '';

    protected string $view = 'capell-deployments::filament.pages.deployment-connection';

    protected static ?string $slug = 'deployment-connection';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-deployments::plugins.deployment_connection.nav_label');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return (string) __('capell-deployments::navigation.system');
    }

    #[Override]
    public static function getNavigationSort(): int
    {
        return 91;
    }

    #[Override]
    public static function getNavigationIcon(): BackedEnum
    {
        return Heroicon::OutlinedServerStack;
    }

    #[Override]
    public static function canAccess(): bool
    {
        if (Gate::allows(self::viewPermission()) || Gate::allows(self::managePermission())) {
            return true;
        }

        $user = auth()->user();
        if ($user?->can(self::viewPermission()) === true) {
            return true;
        }

        return $user?->can(self::managePermission()) === true;
    }

    public static function canManageConnections(): bool
    {
        if (Gate::allows(self::managePermission())) {
            return true;
        }

        return auth()->user()?->can(self::managePermission()) === true;
    }

    #[Override]
    public function getTitle(): string
    {
        return __('capell-deployments::plugins.deployment_connection.title');
    }

    /** @return array<int, DeploymentConnection> */
    public function getConnections(): array
    {
        if (! Schema::hasTable('deployment_connections')) {
            return [];
        }

        return DeploymentConnection::query()->where('is_active', true)->get()->all();
    }

    /**
     * @return array<int, array{label: string, url: string|null, disabledReason: string|null}>
     */
    public function getConnectProviders(): array
    {
        return [
            $this->connectProvider(
                provider: GitProviderType::GitHub,
                label: (string) __('capell-deployments::plugins.deployment_connection.connect_github'),
                url: fn (): string => $this->getGitHubOAuthUrl(),
            ),
            $this->connectProvider(
                provider: GitProviderType::GitLab,
                label: (string) __('capell-deployments::plugins.deployment_connection.connect_gitlab'),
                url: fn (): string => $this->getGitLabOAuthUrl(),
            ),
            $this->connectProvider(
                provider: GitProviderType::Bitbucket,
                label: (string) __('capell-deployments::plugins.deployment_connection.connect_bitbucket'),
                url: fn (): string => $this->getBitbucketOAuthUrl(),
            ),
        ];
    }

    public function getGitHubOAuthUrl(): string
    {
        $this->authorizeManageConnections();
        $this->authorizeRepositorySelection();

        $raw = config('capell-deployments.oauth.github.client_id');
        $clientId = is_string($raw) ? $raw : '';

        return 'https://github.com/login/oauth/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => route('capell-deployments.oauth.github'),
            'scope' => 'repo',
            'state' => CreateOAuthStateAction::run(GitProviderType::GitHub, $this->normalizedRepoOwner(), $this->normalizedRepoName()),
        ]);
    }

    public function getGitLabOAuthUrl(): string
    {
        $this->authorizeManageConnections();
        $this->authorizeRepositorySelection();

        $raw = config('capell-deployments.oauth.gitlab.client_id');
        $clientId = is_string($raw) ? $raw : '';

        return 'https://gitlab.com/oauth/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => route('capell-deployments.oauth.gitlab'),
            'response_type' => 'code',
            'scope' => 'api',
            'state' => CreateOAuthStateAction::run(GitProviderType::GitLab, $this->normalizedRepoOwner(), $this->normalizedRepoName()),
        ]);
    }

    public function getBitbucketOAuthUrl(): string
    {
        $this->authorizeManageConnections();
        $this->authorizeRepositorySelection();

        $raw = config('capell-deployments.oauth.bitbucket.client_id');
        $clientId = is_string($raw) ? $raw : '';

        return 'https://bitbucket.org/site/oauth2/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => route('capell-deployments.oauth.bitbucket'),
            'response_type' => 'code',
            'state' => CreateOAuthStateAction::run(GitProviderType::Bitbucket, $this->normalizedRepoOwner(), $this->normalizedRepoName()),
        ]);
    }

    public function disconnect(int $connectionId): void
    {
        $this->authorizeManageConnections();

        if (! Schema::hasTable('deployment_connections')) {
            return;
        }

        DeploymentConnection::query()
            ->whereKey($connectionId)
            ->where('is_active', true)
            ->delete();

        Notification::make()
            ->title(__('capell-deployments::plugins.deployment_connection.disconnected'))
            ->success()
            ->send();
    }

    private static function viewPermission(): string
    {
        return 'View:' . class_basename(self::class);
    }

    private static function managePermission(): string
    {
        return 'Manage:' . class_basename(self::class);
    }

    private function authorizeManageConnections(): void
    {
        throw_unless(self::canManageConnections(), HttpException::class, 403);
    }

    /**
     * @param  Closure(): string  $url
     * @return array{label: string, url: string|null, disabledReason: string|null}
     */
    private function connectProvider(GitProviderType $provider, string $label, Closure $url): array
    {
        if (! $this->hasRepositorySelection()) {
            return [
                'label' => $label,
                'url' => null,
                'disabledReason' => (string) __('capell-deployments::plugins.deployment_connection.repository_required'),
            ];
        }

        if (! $this->isProviderConfigured($provider)) {
            return [
                'label' => $label,
                'url' => null,
                'disabledReason' => (string) __('capell-deployments::plugins.deployment_connection.provider_not_configured', [
                    'provider' => $provider->getLabel(),
                ]),
            ];
        }

        return [
            'label' => $label,
            'url' => $url(),
            'disabledReason' => null,
        ];
    }

    private function isProviderConfigured(GitProviderType $provider): bool
    {
        $clientId = config(sprintf('capell-deployments.oauth.%s.client_id', $provider->value));

        return is_string($clientId) && trim($clientId) !== '';
    }

    private function hasRepositorySelection(): bool
    {
        return $this->normalizedRepoOwner() !== '' && $this->normalizedRepoName() !== '';
    }

    private function authorizeRepositorySelection(): void
    {
        throw_unless($this->hasRepositorySelection(), HttpException::class, 422);
    }

    private function normalizedRepoOwner(): string
    {
        return trim($this->repoOwner, " \t\n\r\0\x0B/");
    }

    private function normalizedRepoName(): string
    {
        return trim($this->repoName, " \t\n\r\0\x0B/");
    }
}
