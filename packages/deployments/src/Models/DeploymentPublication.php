<?php

declare(strict_types=1);

namespace Capell\Deployments\Models;

use Capell\Deployments\Enums\GitProviderType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property int $deployment_connection_id
 * @property GitProviderType $provider
 * @property string $repo_owner
 * @property string $repo_name
 * @property string $composer_package
 * @property string|null $constraint
 * @property string|null $branch_name
 * @property string|null $commit_sha
 * @property int|null $pull_request_id
 * @property string|null $pull_request_url
 * @property string $status
 * @property bool $dry_run
 * @property CarbonImmutable|null $status_checked_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
final class DeploymentPublication extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'deployment_connection_id',
        'provider',
        'repo_owner',
        'repo_name',
        'composer_package',
        'constraint',
        'branch_name',
        'commit_sha',
        'pull_request_id',
        'pull_request_url',
        'status',
        'dry_run',
        'status_checked_at',
    ];

    public function statusLabel(): string
    {
        return (string) __('capell-deployments::plugins.deployment_connection.publish_statuses.' . $this->status);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'provider' => GitProviderType::class,
            'dry_run' => 'boolean',
            'status_checked_at' => 'datetime',
        ];
    }
}
