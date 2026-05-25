@php
    use Capell\Frontend\Facades\Frontend;
    use Capell\Navigation\Actions\BuildNavigationRenderModelAction;
    use Capell\Navigation\Data\NavigationRenderContextData;
    use Capell\Navigation\Data\NavigationRenderData;
    use Capell\Navigation\Models\Navigation;
    use Capell\Navigation\Support\Loader\NavigationLoader;

    if (! isset($menu)) {
        $menu = null;

        if (isset($block->meta['navigation_id']) && is_numeric($block->meta['navigation_id'])) {
            $menu = NavigationLoader::getNavigationById($block->meta['navigation_id']);
        } elseif (isset($block->meta['navigation']) && is_string($block->meta['navigation'])) {
            $menu = NavigationLoader::getNavigation(
                $block->meta['navigation'],
                Frontend::site(),
                Frontend::language(),
            );
        }
    }

    if (! isset($navigationRenderData)) {
        $navigationRenderData = null;
        if ($menu instanceof Navigation) {
            $navigationRenderData = BuildNavigationRenderModelAction::run(new NavigationRenderContextData(
                navigation: $menu,
                page: Frontend::page(),
                site: Frontend::site(),
                language: Frontend::language(),
                siteDomain: Frontend::site()->siteDomain,
            ));
        }
    }

    if (! isset($items)) {
        $items = $navigationRenderData instanceof NavigationRenderData ? $navigationRenderData->items : collect();
    }
@endphp

@props([
    'container' => '',
    'containerKey',
    'containerWidth' => null,
])
@if ($items->isNotEmpty() || ! config('capell-layout-builder.block.skip_render_empty', true))
    <x-capell-foundation-theme::block.wrapper
        class="capell-navigation-tabs block-navigation-tabs"
        :$container
        :$containerKey
        :$containerWidth
        :index="$loop->index"
        :$block
    >
        <ul
            class="tab-items mt-10 mb-4 flex flex-col flex-wrap items-center gap-4 border-b border-gray-100 px-2 text-center text-sm font-medium text-gray-500 md:flex-row"
        >
            @foreach ($items as $item)
                <li class="tab-item -mb-px">
                    <a
                        href="{{ $item->data['url'] }}"
                        @class([
                            'hover:bg-primary inline-block rounded-t border-b-2 border-transparent px-4 py-3 hover:text-white',
                            'border-b-primary' => $item->active,
                        ])
                        @wireNavigate
                    >
                        {{ $item->label }}
                    </a>
                </li>
            @endforeach
        </ul>
    </x-capell-foundation-theme::block.wrapper>
@endif
