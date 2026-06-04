<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Support\Admin;

use Capell\Admin\Contracts\Extenders\PageEditExtender;
use Capell\SeoSuite\Filament\Widgets\EditPageAuditTabsWidget;
use Filament\Actions\Action;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;

class PageSeoAuditPageEditExtender implements PageEditExtender
{
    /**
     * @return array<int, Action>
     */
    public function getFormActions(): array
    {
        return [];
    }

    /**
     * @return array<int, class-string<Widget>|WidgetConfiguration>
     */
    public function getHeaderWidgets(): array
    {
        return [
            new WidgetConfiguration(EditPageAuditTabsWidget::class, ['record' => null]),
        ];
    }
}
