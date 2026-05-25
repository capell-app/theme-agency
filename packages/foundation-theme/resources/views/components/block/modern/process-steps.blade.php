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
    class="capell-modern-process-steps block-ap-process-steps"
    :$container
    :$containerKey
    :$containerWidth
    :index="$loop->index"
    :$block
>
    <section class="px-6 py-11 md:px-12 md:py-14">
        @if ($block->translation)
            <div class="mx-auto mb-8 max-w-2xl text-center md:mb-10">
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

        @if ($layout === 'horizontal')
            <div class="relative mx-auto max-w-5xl">
                <div
                    class="absolute top-12 right-0 left-0 hidden h-px bg-stone-200 md:block"
                ></div>

                <div class="{{ $responsiveGrid }} md:grid-cols-4 md:gap-6">
                    @forelse ($block->assets as $blockAsset)
                        @php
                            $icon = (string) $blockAsset->asset->getMeta('icon', $loop->index + 1);
                        @endphp

                        <div
                            class="relative min-w-full snap-start rounded-lg border border-stone-200 bg-white p-5 text-center md:min-w-0 md:border-0 md:bg-transparent md:p-0"
                        >
                            <div class="relative z-10 mx-auto mb-4 h-24 w-24">
                                <div
                                    class="flex h-24 w-24 items-center justify-center rounded-full border-2 border-stone-200 bg-white text-blue-700 shadow-sm"
                                >
                                    @if (str_starts_with($icon, 'heroicon-'))
                                        @svg($icon, 'h-8 w-8')
                                    @else
                                        <span class="text-4xl">
                                            {{ $icon }}
                                        </span>
                                    @endif
                                </div>
                                <div
                                    class="absolute -top-1 -right-1 flex h-7 w-7 items-center justify-center rounded-full bg-stone-800 text-xs font-bold text-white"
                                >
                                    {{ $loop->index + 1 }}
                                </div>
                            </div>

                            @if ($blockAsset->asset->translation?->title)
                                <h3
                                    class="mb-1 text-base font-bold text-gray-900"
                                >
                                    {{ $blockAsset->asset->translation->title }}
                                </h3>
                            @endif

                            @if ($blockAsset->asset->translation?->content)
                                <p class="text-sm text-gray-500">
                                    {{ strip_tags($blockAsset->asset->translation->content) }}
                                </p>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center">
                            <p class="text-gray-500">No steps configured.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @else
            <div class="mx-auto max-w-3xl space-y-8">
                @forelse ($block->assets as $blockAsset)
                    @php
                        $icon = (string) $blockAsset->asset->getMeta('icon', $loop->index + 1);
                    @endphp

                    <div class="flex gap-6">
                        <div
                            class="relative flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full border-2 border-stone-200 bg-white text-blue-700 shadow-sm"
                        >
                            @if (str_starts_with($icon, 'heroicon-'))
                                @svg($icon, 'h-6 w-6')
                            @else
                                <span class="text-2xl">{{ $icon }}</span>
                            @endif
                            <div
                                class="absolute -top-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white"
                            >
                                {{ $loop->index + 1 }}
                            </div>
                        </div>

                        <div class="flex-grow pt-2">
                            @if ($blockAsset->asset->translation?->title)
                                <h3
                                    class="mb-1 text-lg font-bold text-gray-900"
                                >
                                    {{ $blockAsset->asset->translation->title }}
                                </h3>
                            @endif

                            @if ($blockAsset->asset->translation?->content)
                                <p class="text-gray-500">
                                    {{ strip_tags($blockAsset->asset->translation->content) }}
                                </p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <p class="text-gray-500">No steps configured.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </section>
</x-capell-foundation-theme::block.wrapper>
