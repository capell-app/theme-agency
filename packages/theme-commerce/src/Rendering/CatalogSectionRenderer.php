<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Commerce\Rendering;

use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Throwable;

final readonly class CatalogSectionRenderer implements SectionRenderer
{
    public function __construct(
        private string $themeKey,
        private bool $shopifyAvailable,
        private bool $failLoudly = false,
    ) {}

    public function themeKey(): string
    {
        return $this->themeKey;
    }

    public function sectionKey(): string
    {
        return 'catalog';
    }

    public function render(ThemeSection $section): string
    {
        if (! function_exists('view')) {
            return '';
        }

        try {
            return view('capell-theme-commerce::sections.catalog', [
                ...$section->toViewData(),
                'shopifyAvailable' => $this->shopifyAvailable,
            ])->render();
        } catch (Throwable $throwable) {
            throw_if($this->failLoudly, $throwable);

            return '';
        }
    }
}
