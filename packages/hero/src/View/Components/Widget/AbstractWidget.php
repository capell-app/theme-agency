<?php

declare(strict_types=1);

namespace Capell\Hero\View\Components\Widget;

use Capell\LayoutBuilder\Models\Widget;
use Closure;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use stdClass;

abstract class AbstractWidget extends Component
{
    protected static string $defaultView = 'capell-hero::components.widget.default';

    protected bool $skipRender = false;

    /**
     * @param  array<array-key, mixed>  $widgetData
     * @param  array<array-key, mixed>  $container
     */
    public function __construct(
        public array $container,
        public string $containerKey,
        public int $widgetIndex,
        public stdClass $loop,
        public Widget $widget,
        public array $widgetData = [],
    ) {
        $this->mountWidget();
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public function render(array $data = []): View|string|Closure
    {
        if ($this->skipRender && config('capell-layout-builder.widget.skip_render_empty', true) === true) {
            return '';
        }

        $data['component_item'] = $this->getComponentItem();

        return resolve(Factory::class)->make($this->componentView(), $data);
    }

    protected function getComponentItem(): ?string
    {
        return $this->widget->getComponentItem();
    }

    protected function mountWidget(): void {}

    private function componentView(): string
    {
        $viewFile = $this->widget->getViewFile();

        if ($viewFile !== null && $viewFile !== '') {
            return $viewFile;
        }

        return static::$defaultView;
    }
}
