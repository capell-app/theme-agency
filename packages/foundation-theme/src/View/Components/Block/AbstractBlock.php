<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\View\Components\Block;

use Capell\FoundationTheme\Support\View\FoundationThemeViewName;
use Capell\Frontend\Facades\Frontend;
use Capell\LayoutBuilder\Models\Block;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use stdClass;
use Throwable;

abstract class AbstractBlock extends Component
{
    protected static string $defaultView = 'capell-foundation-theme::components.block.default';

    protected bool $skipRender = false;

    /**
     * @param  array<array-key, mixed>  $blockData
     * @param  array<array-key, mixed>  $container
     */
    public function __construct(
        public array $container,
        public string $containerKey,
        public int $blockIndex,
        public stdClass $loop,
        public Block $block,
        public array $blockData = [],
        public mixed $pageSlot = null,
    ) {
        $this->mountBlock();
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public function render(array $data = []): View|string|Closure
    {
        $data = [
            'container' => $this->container,
            'containerKey' => $this->containerKey,
            'loop' => $this->loop,
            'language' => $this->frontendContextValue('language'),
            'layout' => $this->frontendContextValue('layout'),
            'pageRecord' => $this->frontendContextValue('page'),
            'site' => $this->frontendContextValue('site'),
            'theme' => $this->frontendContextValue('theme'),
            'urlParams' => $this->frontendParams(),
            'block' => $this->block,
            'blockData' => $this->blockData,
            'blockIndex' => $this->blockIndex,
            'pageSlot' => $this->pageSlot,
            ...$data,
        ];

        if ($this->skipRender && config('capell-layout-builder.block.skip_render_empty', true) === true) {
            return '';
        }

        $data['component_item'] = $this->getComponentItem();

        return view(FoundationThemeViewName::canonical($this->getViewFile()), $data);
    }

    protected function getComponentItem(): ?string
    {
        return $this->block->getComponentItem();
    }

    protected function getViewFile(): string
    {
        return $this->block->getViewFile() ?? static::$defaultView;
    }

    protected function mountBlock(): void {}

    private function frontendContextValue(string $method): mixed
    {
        try {
            return match ($method) {
                'language' => Frontend::language(),
                'layout' => Frontend::layout(),
                'page' => Frontend::page(),
                'site' => Frontend::site(),
                'theme' => Frontend::theme(),
                default => null,
            };
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<array-key, mixed>
     */
    private function frontendParams(): array
    {
        try {
            return Frontend::params();
        } catch (Throwable) {
            return [];
        }
    }
}
