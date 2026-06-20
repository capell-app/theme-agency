<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Widgets;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\SeoSuite\Filament\Widgets\Concerns\BuildsSearchConsolePageTable;
use Filament\Widgets\TableWidget as BaseWidget;

final class TopSearchPagesFilamentWidget extends BaseWidget implements CapellFilamentWidgetContract
{
    use BuildsSearchConsolePageTable;
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['admin', 'super_admin'];

    protected static string $settingsKey = 'seo_top_search_pages';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['xl' => 1];

    protected static ?int $sort = 41;

    protected function mode(): string
    {
        return 'top';
    }

    protected function heading(): string
    {
        return __('capell-seo-suite::dashboard.top_search_pages');
    }
}
