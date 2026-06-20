<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Widgets;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\SeoSuite\Filament\Widgets\Concerns\BuildsSearchConsolePageTable;
use Filament\Widgets\TableWidget as BaseWidget;

final class SearchMovementFilamentWidget extends BaseWidget implements CapellFilamentWidgetContract
{
    use BuildsSearchConsolePageTable;
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['admin', 'super_admin'];

    protected static string $settingsKey = 'seo_search_movement';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['xl' => 1];

    protected static ?int $sort = 42;

    protected function mode(): string
    {
        return 'movement';
    }

    protected function heading(): string
    {
        return __('capell-seo-suite::dashboard.search_movement');
    }
}
