<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\BusinessSolutions\Rendering;

use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Data\ThemePageData;
use Capell\Core\ThemeStudio\Exceptions\SectionRendererNotFoundException;
use Throwable;

final class BusinessSolutionThemeRenderer implements ThemeRenderer
{
    /**
     * @param  array<string, SectionRenderer>  $sectionRenderers
     * @param  array<string, mixed>  $profile
     */
    public function __construct(
        private readonly string $themeKey,
        private readonly string $layoutView,
        private readonly array $sectionRenderers,
        private readonly array $profile,
    ) {}

    public function themeKey(): string
    {
        return $this->themeKey;
    }

    public function render(ThemePageData $page): string
    {
        $html = [];

        foreach ($page->allSections() as $section) {
            $html[] = $this->renderSection($section);
        }

        $content = implode("\n", $html);

        if (! function_exists('view')) {
            return $content;
        }

        try {
            return view($this->layoutView, [
                'brand' => $page->brand,
                'content' => $content,
                'page' => $page,
                'profile' => $this->profile,
            ])->render();
        } catch (Throwable) {
            return $content;
        }
    }

    private function renderSection(ThemeSection $section): string
    {
        $renderer = $this->sectionRenderers[$section->key()] ?? null;

        if ($renderer === null && $section->fallbackKey() !== null) {
            $renderer = $this->sectionRenderers[$section->fallbackKey()] ?? null;
        }

        if ($renderer === null) {
            throw SectionRendererNotFoundException::forSection($this->themeKey, $section->key());
        }

        return $renderer->render($section);
    }
}
