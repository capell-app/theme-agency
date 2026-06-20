<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Widgets;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\SeoSuite\Actions\Dashboard\BuildAiDiscoveryCoverageStatsAction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Override;

final class AiDiscoveryCoverageFilamentWidget extends StatsOverviewWidget implements CapellFilamentWidgetContract
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['admin', 'super_admin'];

    protected static string $settingsKey = 'seo_ai_discovery_coverage';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 45;

    #[Override]
    protected function getStats(): array
    {
        $stats = BuildAiDiscoveryCoverageStatsAction::run();

        return [
            Stat::make(__('capell-seo-suite::dashboard.ai_included_pages'), number_format($stats['included'])),
            Stat::make(__('capell-seo-suite::dashboard.ai_excluded_pages'), number_format($stats['excluded'])),
            Stat::make(__('capell-seo-suite::dashboard.ai_missing_summaries'), number_format($stats['missing_summary'])),
            Stat::make(__('capell-seo-suite::dashboard.ai_stale_markdown'), number_format($stats['stale_markdown'])),
        ];
    }
}
