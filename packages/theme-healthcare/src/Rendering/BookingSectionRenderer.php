<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Healthcare\Rendering;

use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Throwable;

final readonly class BookingSectionRenderer implements SectionRenderer
{
    public function __construct(
        private string $themeKey,
        private bool $formBuilderAvailable,
        private bool $failLoudly = false,
    ) {}

    public function themeKey(): string
    {
        return $this->themeKey;
    }

    public function sectionKey(): string
    {
        return 'booking';
    }

    public function render(ThemeSection $section): string
    {
        if (! function_exists('view')) {
            return '';
        }

        try {
            return view('capell-theme-healthcare::sections.booking', [
                ...$section->toViewData(),
                'formBuilderAvailable' => $this->formBuilderAvailable,
            ])->render();
        } catch (Throwable $throwable) {
            throw_if($this->failLoudly, $throwable);

            return '';
        }
    }
}
