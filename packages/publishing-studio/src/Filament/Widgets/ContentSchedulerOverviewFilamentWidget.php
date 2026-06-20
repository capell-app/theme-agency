<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Filament\Widgets;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\PublishingStudio\Actions\DashboardReports\BuildVisibleContentSchedulerEventsAction;
use Capell\PublishingStudio\Data\SchedulerEventData;
use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;
use Override;

final class ContentSchedulerOverviewFilamentWidget extends StatsOverviewWidget implements CapellFilamentWidgetContract
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['admin', 'super_admin'];

    protected static string $settingsKey = 'content_scheduler';

    protected ?string $heading = null;

    protected int|array|null $columns = [
        'default' => 2,
        'md' => 3,
    ];

    /**
     * @return array<int, Stat>
     */
    #[Override]
    protected function getStats(): array
    {
        $events = BuildVisibleContentSchedulerEventsAction::run();

        return [
            $this->stat($events, SchedulerEventTypeEnum::Publish),
            $this->stat($events, SchedulerEventTypeEnum::Unpublish),
            $this->stat($events, SchedulerEventTypeEnum::Embargo),
            $this->stat($events, SchedulerEventTypeEnum::ReviewReminder),
            Stat::make(
                __('capell-publishing-studio::scheduler.health.failed'),
                $events->filter(fn (SchedulerEventData $event): bool => $event->state === SchedulerEventStateEnum::Failed)->count(),
            )->color('danger'),
            Stat::make(
                __('capell-publishing-studio::scheduler.health.blocked'),
                $events->filter(fn (SchedulerEventData $event): bool => in_array($event->state, [
                    SchedulerEventStateEnum::SkippedEmbargo,
                    SchedulerEventStateEnum::SkippedReleaseWindow,
                    SchedulerEventStateEnum::SkippedStale,
                ], true))->count(),
            )->color('warning'),
        ];
    }

    /**
     * @param  Collection<int, mixed>  $events
     */
    private function stat(Collection $events, SchedulerEventTypeEnum $eventType): Stat
    {
        return Stat::make(
            $eventType->getLabel(),
            $events->filter(fn (SchedulerEventData $event): bool => $event->eventType === $eventType)->count(),
        )
            ->color($eventType->getColor());
    }
}
