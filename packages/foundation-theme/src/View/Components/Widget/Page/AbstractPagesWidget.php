<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\View\Components\Widget\Page;

use Capell\FoundationTheme\View\Components\Widget\AbstractWidget;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Override;

abstract class AbstractPagesWidget extends AbstractWidget
{
    public ?string $componentItem = null;

    /**
     * @var Collection<array-key, mixed>
     */
    public ?Collection $pages = null;

    protected static string $defaultView = 'capell-foundation-theme::components.widget.asset.pages';

    #[Override]
    public function render(array $data = []): View|string|Closure
    {
        if ($this->skipRender && config('capell-layout-builder.widget.skip_render_empty', true) === true) {
            return '';
        }

        return parent::render([
            ...$data,
            'pages' => $this->pages ?? collect(),
        ]);
    }
}
