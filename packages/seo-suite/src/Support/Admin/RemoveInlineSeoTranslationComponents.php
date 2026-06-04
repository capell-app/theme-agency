<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Support\Admin;

use Capell\SeoSuite\Filament\Components\Forms\Page\PageSeoPanel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class RemoveInlineSeoTranslationComponents
{
    /**
     * @param  array<int, mixed>  $components
     * @return array<int, mixed>
     */
    public function __invoke(Schema $schema, array $components): array
    {
        return array_values(array_filter(
            $components,
            fn (mixed $component): bool => ! $this->isInlineSeoComponent($component),
        ));
    }

    private function isInlineSeoComponent(mixed $component): bool
    {
        if ($component instanceof PageSeoPanel) {
            return true;
        }

        return $component instanceof Section
            && $component->getHeading() === __('capell-admin::tab.seo_settings');
    }
}
