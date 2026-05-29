<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Support\Admin;

use Capell\Admin\Contracts\Extenders\PageEditExtender;
use Capell\SeoSuite\Filament\Widgets\EditPagePageSpeedAuditWidget;
use Capell\SeoSuite\Filament\Widgets\EditPageSeoAuditWidget;
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
            new WidgetConfiguration(EditPageSeoAuditWidget::class, ['record' => null]),
            new WidgetConfiguration(EditPagePageSpeedAuditWidget::class, ['record' => null]),
        ];
    }
}
