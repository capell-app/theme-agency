<?php

declare(strict_types=1);

namespace Capell\Tests\Packages\Fixtures;

use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;

final class ThemeFrontendStringSectionRenderer implements SectionRenderer
{
    public function __construct(
        private readonly string $themeKey,
        private readonly string $sectionKey,
    ) {}

    public function themeKey(): string
    {
        return $this->themeKey;
    }

    public function sectionKey(): string
    {
        return $this->sectionKey;
    }

    public function render(ThemeSection $section): string
    {
        $viewData = $section->toViewData();
        $sectionData = $viewData['section'] ?? $section;
        $heading = data_get($sectionData, 'heading', data_get($sectionData, 'brandName', $section->key()));

        return sprintf(
            '<section data-theme="%s" data-section="%s"><h2>%s</h2></section>',
            e($this->themeKey),
            e($section->key()),
            e(is_string($heading) ? $heading : $section->key()),
        );
    }
}
