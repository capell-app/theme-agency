<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Manufacturing\Support\Screenshots;

use Capell\Core\ThemeStudio\Contracts\ThemeSection;

final class ManufacturingScreenshotSection implements ThemeSection
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
     * @return array<string, mixed>
     */
    public function toViewData(): array
    {
        return ['section' => $this];
    }
}
