<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CallOut\View\Components;

use Capell\ThemeStudio\CallOut\Support\BlogAvailability;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Provider-resolved optional-integration fallback for a "latest tips" blog
 * rail: a real PHP class component that decides, at render time, whether
 * `capell-app/blog` is installed. When it is not, this component renders
 * nothing at all (an empty rail is worse than no rail) rather than a
 * generic empty-state placeholder — see `Capell\ThemeStudio\CallOut\Support\BlogAvailability`.
 */
final class BlogTipsRail extends Component
{
    public bool $available;

    /**
     * @param  list<array{title: string, summary: string, url: string}>  $items
     */
    public function __construct(
        public readonly string $heading,
        public readonly array $items = [],
    ) {
        $this->available = BlogAvailability::check() && $items !== [];
    }

    public function render(): View
    {
        return view('capell-theme-call-out::components.blog-tips-rail');
    }
}
