<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LiquidGlass\Support\Screenshots;

use Capell\Core\ThemeStudio\Contracts\ThemeSection;

final class LiquidGlassScreenshotSection implements ThemeSection
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        private readonly string $sectionKey,
        private readonly array $data = [],
    ) {}

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function key(): string
    {
        return $this->sectionKey;
    }

    public function fallbackKey(): ?string
    {
        return null;
    }

    /**
     * The section view is exposed both as the `$section` object (for views that
     * read `$section->heading`) and as flat variables (for views such as
     * content-listing that read bare `$heading`, `$summary`, `$items`, and
     * `$variant`).
     *
     * @return array<string, mixed>
     */
    public function toViewData(): array
    {
        return [...$this->data, 'section' => $this];
    }
}
