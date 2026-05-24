@props([
    'currency' => $block->getMeta('currency', '$'),
    'billingOptions' => $block->getMeta('billing_options', 'monthly'),
    'container',
    'containerKey',
    'containerWidth' => null,
    'loop',
    'block',
])

<x-capell-foundation-theme::block.wrapper
    class="capell-modern-pricing-table block-ap-pricing-table"
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
                        class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl"
                    >
                        {{ $block->translation->title }}
                    </h2>
                @endif

                @if ($block->translation->content)
                    <p class="mt-3 text-lg text-gray-500">
                        {{ strip_tags($block->translation->content) }}
                    </p>
                @endif
            </div>
        @endif

        @if ($billingOptions === 'both')
            <div class="mb-12 flex items-center justify-center gap-4">
                <span class="text-gray-700">Monthly</span>
                {{-- format-ignore-start --}}
                        <button
                            class="billing-toggle-button relative h-8 w-14 rounded-full bg-stone-800 transition-colors"
                            data-billing-toggle
                        >
                            <div
                                class="billing-toggle-dot absolute left-1 top-1 h-6 w-6 rounded-full bg-white transition-all"
                            ></div>
                        </button>
                        {{-- format-ignore-end --}}
                <span class="text-gray-700">Annual</span>
                <span class="text-sm font-semibold text-emerald-700">
                    Save 17%
                </span>
            </div>
        @endif

        <div
            class="pricing-grid mx-auto grid max-w-5xl grid-cols-1 gap-6 md:grid-cols-3"
            data-billing="{{ $billingOptions === 'both' ? 'monthly' : $billingOptions }}"
        >
            @forelse ($block->assets as $blockAsset)
                @php
                    $price = $blockAsset->asset->getMeta('price', '0');
                    $priceAnnual = $blockAsset->asset->getMeta('price_annual', $price);
                    $featured = (bool) $blockAsset->asset->getMeta('featured', false);
                    $ctaLabel = $blockAsset->asset->getMeta('cta_label', 'Get Started');
                    $ctaUrl = $blockAsset->asset->getMeta('cta_url', '#');
                    $features = $blockAsset->asset->getMeta('features', []);
                @endphp

                @if ($featured)
                    <div
                        class="pricing-plan relative rounded-xl bg-stone-900 p-8 text-white shadow-xl md:-my-4"
                        data-price-monthly="{{ $price }}"
                        data-price-annual="{{ $priceAnnual }}"
                    >
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span
                                class="rounded-full bg-amber-400 px-4 py-1 text-xs font-bold text-amber-900"
                            >
                                Most Popular
                            </span>
                        </div>

                        @if ($blockAsset->asset->translation?->title)
                            <h3 class="mb-1 text-2xl font-bold tracking-tight text-white">
                                {{ $blockAsset->asset->translation->title }}
                            </h3>
                        @endif

                        @if ($blockAsset->asset->translation?->content)
                            <p class="mb-6 text-sm text-stone-400">
                                {{ strip_tags($blockAsset->asset->translation->content) }}
                            </p>
                        @endif

                        <div class="price-container mb-6">
                            <span class="plan-price text-4xl font-bold text-white">
                                {{ $price !== 'Custom' ? $currency : '' }}{{ $price }}
                            </span>
                            @if ($price !== 'Custom')
                                <span class="billing-period text-stone-400">/month</span>
                            @endif
                        </div>

                        @if (count($features) > 0)
                            <ul class="mb-8 space-y-3">
                                @foreach ($features as $feature)
                                    <li class="flex items-center gap-2 text-stone-200">
                                        <span class="font-bold text-emerald-400">✓</span>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <a
                            href="{{ $ctaUrl }}"
                            class="block w-full rounded-lg bg-white px-6 py-3 text-center font-semibold text-stone-900 transition-opacity hover:opacity-90"
                        >
                            {{ $ctaLabel }}
                        </a>
                    </div>
                @else
                    <div
                        class="pricing-plan relative rounded-xl border border-stone-200 bg-white p-8"
                        data-price-monthly="{{ $price }}"
                        data-price-annual="{{ $priceAnnual }}"
                    >
                        @if ($blockAsset->asset->translation?->title)
                            <h3 class="mb-1 text-2xl font-bold tracking-tight text-gray-900">
                                {{ $blockAsset->asset->translation->title }}
                            </h3>
                        @endif

                        @if ($blockAsset->asset->translation?->content)
                            <p class="mb-6 text-sm text-gray-500">
                                {{ strip_tags($blockAsset->asset->translation->content) }}
                            </p>
                        @endif

                        <div class="price-container mb-6">
                            <span class="plan-price text-4xl font-bold text-gray-900">
                                {{ $price !== 'Custom' ? $currency : '' }}{{ $price }}
                            </span>
                            @if ($price !== 'Custom')
                                <span class="billing-period text-gray-500">/month</span>
                            @endif
                        </div>

                        @if (count($features) > 0)
                            <ul class="mb-8 space-y-3">
                                @foreach ($features as $feature)
                                    <li class="flex items-center gap-2 text-gray-700">
                                        <span class="font-bold text-emerald-600">✓</span>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <a
                            href="{{ $ctaUrl }}"
                            class="block w-full rounded-lg border border-stone-200 bg-white px-6 py-3 text-center font-semibold text-gray-700 transition-colors hover:border-emerald-300 hover:text-emerald-700"
                        >
                            {{ $ctaLabel }}
                        </a>
                    </div>
                @endif
            @empty
                <div class="col-span-full py-12 text-center">
                    <p class="text-gray-500">No pricing plans configured</p>
                </div>
            @endforelse
        </div>
    </section>
</x-capell-foundation-theme::block.wrapper>
