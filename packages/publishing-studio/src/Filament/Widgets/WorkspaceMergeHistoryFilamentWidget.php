<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Filament\Widgets;

use Capell\Admin\Concerns\CachesDashboardQuery;
use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\PublishingStudio\Actions\Dashboard\BuildWorkspaceMergeHistoryAction;
use Capell\PublishingStudio\Data\Dashboard\MergeHistoryEntryData;
use Capell\PublishingStudio\Data\Dashboard\WorkspaceMergeHistoryData;
use Capell\PublishingStudio\Support\WorkspaceSchema;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;
use Override;
use Spatie\LaravelData\DataCollection;

final class WorkspaceMergeHistoryFilamentWidget extends Widget implements CapellFilamentWidgetContract
{
    use CachesDashboardQuery;
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['super_admin'];

    protected static string $settingsKey = 'workspace_merge_history';

    protected string $view = 'capell-publishing-studio::widgets.workspace-merge-history';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['md' => 2];

    #[Override]
    public static function canView(): bool
    {
        return WorkspaceSchema::isReady() && self::canViewCheck();
    }

    #[Computed(persist: true, seconds: 300)]
    public function data(): WorkspaceMergeHistoryData
    {
        if (! WorkspaceSchema::isReady()) {
            return new WorkspaceMergeHistoryData(
                entries: MergeHistoryEntryData::collect([], DataCollection::class),
            );
        }

        return $this->cacheQueryResult(
            fn (): WorkspaceMergeHistoryData => BuildWorkspaceMergeHistoryAction::run(),
            'dashboard:workspace-merge-history',
        );
    }
}
