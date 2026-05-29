<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Nonprofit\Rendering;

use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Illuminate\Contracts\View\Factory;
use Throwable;

final readonly class PackageAwareSectionRenderer implements SectionRenderer
{
    /**
     * @param  array<string, bool>  $integrations
     */
    public function __construct(
        private string $themeKey,
        private string $sectionKey,
        private string $view,
        private array $integrations = [],
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
            return resolve(Factory::class)->make($this->view, [
                ...$section->toViewData(),
                ...$this->integrations,
            ])->render();
        } catch (Throwable $throwable) {
            throw_if($this->failLoudly, $throwable);

            return '';
        }
    }
}
