<?php

declare(strict_types=1);

namespace Capell\Deployments\Filament\Pages;

use BackedEnum;
use Capell\Deployments\Actions\OAuth\CreateOAuthStateAction;
use Capell\Deployments\Enums\GitProviderType;
use Capell\Deployments\Models\DeploymentConnection;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Override;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class DeploymentConnectionPage extends Page
{
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

    public function getGitHubOAuthUrl(): string
    {
        $this->authorizeManageConnections();

        $raw = config('capell-deployments.oauth.github.client_id');
        $clientId = is_string($raw) ? $raw : '';

        return 'https://github.com/login/oauth/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => route('capell-deployments.oauth.github'),
            'scope' => 'repo',
            'state' => CreateOAuthStateAction::run(GitProviderType::GitHub),
        ]);
    }

    public function getGitLabOAuthUrl(): string
    {
        $this->authorizeManageConnections();

        $raw = config('capell-deployments.oauth.gitlab.client_id');
        $clientId = is_string($raw) ? $raw : '';

        return 'https://gitlab.com/oauth/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => route('capell-deployments.oauth.gitlab'),
            'response_type' => 'code',
            'scope' => 'api',
            'state' => CreateOAuthStateAction::run(GitProviderType::GitLab),
        ]);
    }

    public function getBitbucketOAuthUrl(): string
    {
        $this->authorizeManageConnections();

        $raw = config('capell-deployments.oauth.bitbucket.client_id');
        $clientId = is_string($raw) ? $raw : '';

        return 'https://bitbucket.org/site/oauth2/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => route('capell-deployments.oauth.bitbucket'),
            'response_type' => 'code',
            'state' => CreateOAuthStateAction::run(GitProviderType::Bitbucket),
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
}
