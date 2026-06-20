<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Widgets;

use Capell\Admin\Filament\Concerns\HasBlankPlaceholder;
use Capell\SeoSuite\Filament\Widgets\Concerns\ResolvesEditPageRecord;
use Filament\Widgets\Widget;

class EditPageAuditTabsFilamentWidget extends Widget
{
    use HasBlankPlaceholder;
    use ResolvesEditPageRecord;

    public string $activeTab = 'seo';

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'capell-seo-suite::filament.widgets.edit-page-audit-tabs';

    public function selectTab(string $tab): void
    {
        if (! in_array($tab, ['seo', 'pagespeed'], true)) {
            return;
        }

        $this->activeTab = $tab;
    }
}
