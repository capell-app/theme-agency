<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Support\Admin;

use Capell\Admin\Contracts\Extenders\PageEditExtender;
use Filament\Actions\Action;
use Filament\Widgets\Widget;

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
     * @return array<int, class-string<Widget>>
     */
    public function getHeaderWidgets(): array
    {
        return [];
    }
}
