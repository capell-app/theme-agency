<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Healthcare\Rendering;

use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Throwable;

final readonly class BlogTeaserSectionRenderer implements SectionRenderer
{
    public function __construct(
        private string $themeKey,
        private string $sectionKey,
        private bool $blogAvailable,
        private bool $failLoudly = false,
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
        if (! function_exists('view')) {
            return '';
        }

        try {
            return view('capell-theme-healthcare::sections.blog-teaser', [
                ...$section->toViewData(),
                'blogAvailable' => $this->blogAvailable,
            ])->render();
        } catch (Throwable $throwable) {
            throw_if($this->failLoudly, $throwable);

            return '';
        }
    }
}
