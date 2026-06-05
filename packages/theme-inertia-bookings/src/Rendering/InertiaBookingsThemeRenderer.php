<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InertiaBookings\Rendering;

use Capell\Core\ThemeStudio\Contracts\ThemeRenderer;
use Capell\Core\ThemeStudio\Data\ThemePageData;
use Capell\ThemeStudio\InertiaBookings\Providers\InertiaBookingsThemeServiceProvider;

class InertiaBookingsThemeRenderer implements ThemeRenderer
{
    public function themeKey(): string
    {
        return InertiaBookingsThemeServiceProvider::THEME_KEY;
    }

    public function render(ThemePageData $page): string
    {
        return '';
    }
}
