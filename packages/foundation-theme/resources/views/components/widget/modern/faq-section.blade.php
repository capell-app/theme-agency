@props([
    'container',
    'containerKey',
    'containerWidth' => null,
    'loop',
    'widget',
])

@php
    $categories = $widget->assets
        ->map(function (object $widgetAsset): ?string {
            $widgetAssetRelations = method_exists($widgetAsset, 'getRelations') ? $widgetAsset->getRelations() : [];
            $asset = $widgetAssetRelations['asset'] ?? null;
            $category = $asset !== null ? $asset->getMeta('category') : null;

            return is_string($category) && $category !== '' ? $category : null;
        })
        ->filter()
        ->unique()
        ->values()
        ->toArray();

    $hasCategories = count($categories) > 0;
@endphp

<x-capell-foundation-theme::widget.wrapper
    class="capell-modern-faq-section widget-ap-faq-section"
    :$container
    :$containerKey
    :$containerWidth
    :index="$loop->index"
    :$widget
>
    <section class="px-6 py-12 md:px-12 md:py-16">
        @if ($widget->translation)
            <div class="mx-auto mb-12 max-w-2xl text-center">
                @if ($widget->translation->title)
                    <h2
                        class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl"
                    >
                        {{ $widget->translation->title }}
                    </h2>
                @endif

                @if ($widget->translation->content)
                    <p class="mt-3 text-lg text-gray-500">
                        {{ strip_tags($widget->translation->content) }}
                    </p>
                @endif
            </div>
        @endif

        @if ($hasCategories)
            <div
                class="mx-auto mb-8 flex max-w-3xl flex-wrap justify-center gap-2"
            >
                <button
                    class="faq-category-tab rounded-full bg-stone-800 px-4 py-2 text-sm font-semibold text-white transition-all"
                    data-category="all"
                    data-faq-category-tab
                >
                    All
                </button>

                @foreach ($categories as $category)
                    <button
                        class="faq-category-tab rounded-full border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition-all hover:border-stone-400 hover:text-stone-800"
                        data-category="{{ $category }}"
                        data-faq-category-tab
                    >
                        {{ $category }}
                    </button>
                @endforeach
            </div>
        @endif

        <div class="faq-container mx-auto max-w-3xl space-y-3">
            @forelse ($widget->assets as $widgetAsset)
                @php
                    $widgetAssetRelations = method_exists($widgetAsset, 'getRelations') ? $widgetAsset->getRelations() : [];
                    $asset = $widgetAssetRelations['asset'] ?? null;
                    $assetRelations = $asset !== null && method_exists($asset, 'getRelations') ? $asset->getRelations() : [];
                    $assetTranslation = $assetRelations['translation'] ?? null;
                @endphp

                @continue($asset === null)

                <details
                    class="faq-item group rounded-xl border border-stone-200 bg-white"
                    data-category="{{ $asset->getMeta('category', 'uncategorized') }}"
                >
                    <summary
                        class="flex cursor-pointer items-center justify-between p-5 text-base font-semibold text-gray-900 select-none"
                    >
                        <span>
                            {{ $assetTranslation?->title }}
                        </span>
                        <span
                            class="ml-4 flex-shrink-0 text-xl text-stone-500 transition-transform group-open:rotate-45"
                        >
                            +
                        </span>
                    </summary>

                    @if ($assetTranslation?->content)
                        <div
                            class="border-t border-stone-100 px-5 pt-4 pb-5 leading-relaxed text-stone-600"
                        >
                            {{ strip_tags($assetTranslation->content) }}
                        </div>
                    @endif
                </details>
            @empty
                <div class="py-12 text-center">
                    <p class="text-gray-500">No FAQs configured</p>
                </div>
            @endforelse
        </div>
    </section>
</x-capell-foundation-theme::widget.wrapper>
