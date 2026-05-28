<?php

declare(strict_types=1);

namespace Capell\Hero\View\Components\Block;

use Capell\LayoutBuilder\Models\Widget;
use Closure;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use stdClass;

abstract class AbstractBlock extends Component
{
    protected static string $defaultView = 'capell-hero::components.block.default';

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
        public Widget $block,
        public array $blockData = [],
    ) {
        $this->mountBlock();
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public function render(array $data = []): View|string|Closure
    {
        if ($this->skipRender && config('capell-layout-builder.block.skip_render_empty', true) === true) {
            return '';
        }

        $data['component_item'] = $this->getComponentItem();

        return resolve(Factory::class)->make($this->componentView(), $data);
    }

    protected function getComponentItem(): ?string
    {
        return $this->block->meta['component_item'] ?? $this->block->type->meta['component_item'] ?? null;
    }

    protected function mountBlock(): void {}

    private function componentView(): string
    {
        if (isset($this->block->meta['view_file']) && $this->block->meta['view_file'] !== '') {
            return $this->block->meta['view_file'];
        }

        if (isset($this->block->type->meta['view_file']) && $this->block->type->meta['view_file'] !== '') {
            return $this->block->type->meta['view_file'];
        }

        return static::$defaultView;
    }
}
