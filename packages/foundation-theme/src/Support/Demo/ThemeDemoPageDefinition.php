<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;

final class ThemeDemoPageDefinition
{
    /**
     * @param  array<string, mixed>  $renderData
     */
    public function __construct(
        public readonly string $surface,
        public readonly string $name,
        public readonly string $title,
        public readonly string $slug,
        public readonly string $content,
        public readonly array $renderData,
        public readonly PageTypeEnum $type = PageTypeEnum::Default,
        public readonly LayoutEnum $layout = LayoutEnum::Default,
    ) {}
}
