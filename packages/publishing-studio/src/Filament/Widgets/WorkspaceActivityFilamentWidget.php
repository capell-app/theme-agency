<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Filament\Widgets;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\PublishingStudio\Actions\Dashboard\BuildWorkspaceActivityAction;
use Capell\PublishingStudio\Data\Dashboard\WorkspaceActivityData;
use Capell\PublishingStudio\Data\Dashboard\WorkspaceMergeData;
use Capell\PublishingStudio\Support\WorkspaceSchema;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;
use Override;
use Spatie\LaravelData\DataCollection;

final class WorkspaceActivityFilamentWidget extends Widget implements CapellFilamentWidgetContract
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['admin', 'super_admin'];

    protected static string $settingsKey = 'workspace_activity';

    protected string $view = 'capell-publishing-studio::widgets.workspace-activity';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = [
        'default' => 12,
        'lg' => 1,
    ];

    #[Override]
    public static function canView(): bool
    {
        return WorkspaceSchema::isReady() && self::canViewCheck();
    }

    #[Computed(persist: true, seconds: 60)]
    public function data(): WorkspaceActivityData
    {
        if (! WorkspaceSchema::isReady()) {
            return new WorkspaceActivityData(
                pendingApprovalsCount: 0,
                stuckCount: 0,
                recentMerges: WorkspaceMergeData::collect([], DataCollection::class),
            );
        }

        $user = auth()->user();

        if ($user === null) {
            return new WorkspaceActivityData(
                pendingApprovalsCount: 0,
                stuckCount: 0,
                recentMerges: WorkspaceMergeData::collect([], DataCollection::class),
            );
        }

        return BuildWorkspaceActivityAction::run($user);
    }
}
