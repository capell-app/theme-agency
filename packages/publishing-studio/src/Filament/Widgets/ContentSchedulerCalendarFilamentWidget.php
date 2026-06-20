<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Filament\Widgets;

use Capell\PublishingStudio\Actions\DashboardReports\BuildVisibleEditorialCalendarEventsAction;
use Capell\PublishingStudio\Data\EditorialCalendarEventData;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

final class ContentSchedulerCalendarFilamentWidget extends Widget
{
    protected string $view = 'capell-publishing-studio::widgets.content-scheduler-calendar';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return Collection<string, Collection<int, EditorialCalendarEventData>>
     */
    #[Computed]
    public function eventsByDate(): Collection
    {
        return BuildVisibleEditorialCalendarEventsAction::run()
            ->groupBy(fn (EditorialCalendarEventData $event): string => $event->startsAt->format('Y-m-d'));
    }
}
