@props([
    'container' => '',
    'containerKey',
    'containerWidth' => null,
    'items' => collect(),
    'loop',
    'block',
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
