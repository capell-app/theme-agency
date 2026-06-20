<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Support\Dashboard;

use Capell\Admin\Enums\DashboardEnum;
use Capell\DashboardReports\Filament\Widgets\ContentHealthFilamentWidget;
use Capell\DashboardReports\Filament\Widgets\PublishingTrendChartFilamentWidget;
use Illuminate\Support\Collection;

final class DashboardReportWidgetRegistry
{
    /**
     * @var array<string, array{widget: class-string, dashboards: list<DashboardEnum>}>
     */
    private array $widgets = [];

    public function __construct()
    {
        $this->register(PublishingTrendChartFilamentWidget::class, DashboardEnum::Main);
        $this->register(ContentHealthFilamentWidget::class, DashboardEnum::Main);
    }

    /**
     * @param  class-string  $widget
     */
    public function register(string $widget, DashboardEnum ...$dashboards): self
    {
        $dashboards = $dashboards === [] ? [DashboardEnum::Main] : array_values($dashboards);

        $this->widgets[$this->key($widget, $dashboards)] = [
            'widget' => $widget,
            'dashboards' => $dashboards,
        ];

        return $this;
    }

    /**
     * @return Collection<int, array{widget: class-string, dashboards: list<DashboardEnum>}>
     */
    public function registrations(): Collection
    {
        return collect(array_values($this->widgets));
    }

    /**
     * @param  class-string  $widget
     * @param  list<DashboardEnum>  $dashboards
     */
    private function key(string $widget, array $dashboards): string
    {
        $dashboardKeys = collect($dashboards)
            ->map(static fn (DashboardEnum $dashboard): string => $dashboard->value)
            ->sort()
            ->implode('|');

        return $widget . ':' . $dashboardKeys;
    }
}
