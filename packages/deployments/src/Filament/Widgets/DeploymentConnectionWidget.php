<?php

declare(strict_types=1);

namespace Capell\Deployments\Filament\Widgets;

use Capell\Deployments\Filament\Pages\DeploymentConnectionPage;
use Capell\Deployments\Models\DeploymentConnection;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Schema;
use Override;

final class DeploymentConnectionWidget extends Widget
{
    protected string $view = 'capell-deployments::filament.widgets.deployment-connection';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['md' => 1];

    protected static ?int $sort = 12;

    #[Override]
    public static function canView(): bool
    {
        return DeploymentConnectionPage::canAccess();
    }

    public function getConnection(): ?DeploymentConnection
    {
        if (! self::canView() || ! Schema::hasTable('deployment_connections')) {
            return null;
        }

        return DeploymentConnection::query()->where('is_active', true)->first();
    }
}
