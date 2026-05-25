@props([
    'layout' => $block->getMeta('layout', 'horizontal'),
    'container',
    'containerKey',
    'containerWidth' => null,
    'loop',
    'block',
])

@php
    $responsiveGrid = '!flex snap-x [scrollbar-width:none] gap-4 !overflow-x-auto pb-3 md:!grid md:!overflow-visible md:pb-0 [&::-webkit-scrollbar]:hidden';
@endphp

<x-capell-foundation-theme::block.wrapper
    class="capell-modern-stats-section block-ap-stats-section"
    :$container
    :$containerKey
    :$containerWidth
    :index="$loop->index"
    :$block
>
    <section class="px-6 py-12 md:px-12 md:py-16">
        @if ($block->translation)
            <div class="mx-auto mb-12 max-w-2xl text-center">
                @if ($block->translation->title)
                    <h2
                        class="mb-3 text-3xl font-bold tracking-tight text-gray-900 md:text-4xl"
                    >
                        {{ $block->translation->title }}
                    </h2>
                @endif

                @if ($block->translation->content)
                    <p class="text-lg text-gray-500">
                        {{ strip_tags($block->translation->content) }}
                    </p>
                @endif
            </div>
        @endif

        <div
            @class([
                'mx-auto',
                'grid max-w-md grid-cols-1 gap-6' => $layout === 'vertical',
                'max-w-5xl ' . $responsiveGrid . ' md:grid-cols-4' => $layout !== 'vertical',
            ])
        >
            @forelse ($block->assets as $blockAsset)
                @php
                    $icon = (string) $blockAsset->asset->getMeta('icon', '');
                @endphp

                <div
                    class="min-w-full snap-start rounded-xl border border-stone-200 bg-white p-6 text-center md:min-w-0 md:p-8"
                >
                    @if ($icon !== '')
                        <div
                            class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 text-blue-700"
                        >
                            @if (str_starts_with($icon, 'heroicon-'))
                                @svg($icon, 'h-6 w-6')
                            @else
                                <span class="text-3xl">{{ $icon }}</span>
                            @endif
                        </div>
                    @endif

                    @if ($blockAsset->asset->translation?->content)
                        <p
                            class="mb-1 text-3xl font-bold text-emerald-700 md:text-4xl"
                        >
                            {{ strip_tags($blockAsset->asset->translation->content) }}
                        </p>
                    @endif

                    @if ($blockAsset->asset->translation?->title)
                        <p class="text-sm font-medium text-gray-500">
                            {{ $blockAsset->asset->translation->title }}
                        </p>
                    @endif
                </div>
            @empty
                <div class="col-span-full py-12 text-center">
                    <p class="text-gray-500">No stats configured.</p>
                </div>
            @endforelse
        </div>
    </section>
</x-capell-foundation-theme::block.wrapper>
