@props([
    'layout' => $block->getMeta('layout', 'grid'),
    'container',
    'containerKey',
    'containerWidth' => null,
    'loop',
    'block',
])

@php
    $responsiveGrid = '!flex snap-x [scrollbar-width:none] gap-4 !overflow-x-auto pb-3 md:!grid md:!overflow-visible md:pb-0 [&::-webkit-scrollbar]:hidden';
    $responsiveItem = 'min-w-full snap-start md:min-w-0';
@endphp

<x-capell-foundation-theme::block.wrapper
    class="capell-modern-feature-list block-ap-feature-list"
    :$container
    :$containerKey
    :$containerWidth
    :index="$loop->index"
    :$block
>
    <section class="ap-showcase-feature-list capell-showcase">
        <div class="capell-showcase__inner">
            @if ($block->translation)
                <div class="capell-showcase__section-head">
                    @if ($block->translation->title)
                        <h2
                            class="ap-feature-list-headline capell-showcase__heading"
                        >
                            {{ $block->translation->title }}
                        </h2>
                    @endif

                    @if ($block->translation->content)
                        <p
                            class="ap-feature-list-description capell-showcase__copy"
                        >
                            {!! strip_tags($block->translation->content) !!}
                        </p>
                    @endif
                </div>
            @endif

            <div
                @class([
                    'ap-feature-list' => $layout === 'vertical',
                    'ap-feature-grid ' . $responsiveGrid => $layout !== 'vertical',
                ])
            >
                @forelse ($block->assets as $blockAsset)
                    @php
                        $blockAssetRelations = method_exists($blockAsset, 'getRelations') ? $blockAsset->getRelations() : [];
                        $asset = $blockAssetRelations['asset'] ?? null;
                        $assetRelations = $asset !== null && method_exists($asset, 'getRelations') ? $asset->getRelations() : [];
                        $assetTranslation = $assetRelations['translation'] ?? null;
                        $icon = $asset !== null ? (string) $asset->getMeta('icon', '') : '';
                    @endphp

                    @continue($asset === null)

                    <article
                        @class([
                            'ap-feature-item layout-builder-card',
                            $responsiveItem => $layout !== 'vertical',
                        ])
                    >
                        @if ($icon !== '')
                            <span class="ap-feature-item__icon">
                                @if (str_starts_with($icon, 'heroicon-'))
                                    @svg($icon, 'h-5 w-5')
                                @else
                                    {{ $icon }}
                                @endif
                            </span>
                        @endif

                        @if ($assetTranslation?->title)
                            <h3 class="ap-feature-title ap-feature-item__title">
                                {{ $assetTranslation->title }}
                            </h3>
                        @endif

                        @if ($assetTranslation?->content)
                            <p
                                class="ap-feature-description ap-feature-item__description"
                            >
                                {{ strip_tags($assetTranslation->content) }}
                            </p>
                        @endif
                    </article>
                @empty
                    <div class="py-12 text-slate-300">
                        No features configured.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-capell-foundation-theme::block.wrapper>
