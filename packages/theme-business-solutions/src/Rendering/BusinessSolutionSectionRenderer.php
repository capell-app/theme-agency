<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\BusinessSolutions\Rendering;

use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Throwable;

final class BusinessSolutionSectionRenderer implements SectionRenderer
{
    /**
     * @param  array<string, mixed>  $profile
     */
    public function __construct(
        private readonly string $themeKey,
        private readonly string $sectionKey,
        private readonly string $view,
        private readonly array $profile,
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
            return view($this->view, [
                ...$section->toViewData(),
                'profile' => $this->profile,
            ])->render();
        } catch (Throwable $throwable) {
            report($throwable);

            return '';
        }
    }
}
