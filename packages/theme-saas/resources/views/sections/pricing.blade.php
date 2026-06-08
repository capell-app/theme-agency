@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-saas::generic.pricing_label');
    $summary ??= $section->summary ?? null;
    $featureRows = collect($items)
        ->flatMap(static fn (array $item): array => array_keys($item['features'] ?? []))
        ->unique()
        ->values()
        ->all();
@endphp

<section class="theme-section theme-section-pricing bg-slate-50">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-cyan-700 uppercase"
                >
                    {{ __('capell-theme-saas::generic.pricing_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black text-slate-950">
                    {{ $heading }}
                </h2>
            </div>
            @if ($summary)
                <p class="text-base leading-8 text-slate-600">
                    {{ $summary }}
                </p>
            @endif

            <p class="text-sm font-bold text-slate-600">
                {{ $contentSectionsAvailable ?? false ? __('capell-theme-saas::generic.pricing_connected') : __('capell-theme-saas::generic.pricing_static') }}
            </p>
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @forelse ($items as $item)
                <article
                    class="@if ($item['popular'] ?? false) ring-2 ring-cyan-500 @endif grid min-h-full gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-4">
                        <p class="text-xs font-black text-cyan-700 uppercase">
                            {{ $item['type'] ?? __('capell-theme-saas::generic.pricing_label') }}
                        </p>
                        @if ($item['popular'] ?? false)
                            <span
                                class="rounded-full bg-cyan-50 px-3 py-1 text-[0.68rem] font-black text-cyan-800 uppercase"
                            >
                                {{ __('capell-theme-saas::generic.popular_plan_label') }}
                            </span>
                        @endif
                    </div>
                    <h3 class="text-xl font-black text-slate-950">
                        {{ $item['title'] ?? __('capell-theme-saas::generic.product_signal') }}
                    </h3>
                    @if ($item['price'] ?? null)
                        <p
                            class="flex items-end gap-2 text-4xl font-black text-slate-950"
                        >
                            <span>{{ $item['price'] }}</span>
                            <span class="pb-1 text-sm font-bold text-slate-500">
                                {{ $item['period'] ?? __('capell-theme-saas::generic.monthly_period_label') }}
                            </span>
                        </p>
                    @endif

                    <p class="text-sm leading-6 text-slate-600">
                        {{ $item['summary'] ?? __('capell-theme-saas::generic.pricing_ready') }}
                    </p>
                    @if ($item['cta'] ?? null)
                        <a
                            href="{{ $item['url'] ?? '#' }}"
                            class="mt-auto inline-flex items-center justify-center rounded-lg border border-slate-950 bg-slate-950 px-4 py-3 text-sm font-black text-white"
                        >
                            {{ $item['cta'] }}
                        </a>
                    @endif
                </article>
            @empty
                <article
                    class="border border-dashed border-slate-300 bg-white p-6"
                >
                    <h3 class="text-lg font-black text-slate-950">
                        {{ __('capell-theme-saas::generic.premium_layout_ready') }}
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ __('capell-theme-saas::generic.premium_layout_empty') }}
                    </p>
                </article>
            @endforelse
        </div>

        @if ($featureRows !== [])
            <div
                class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="saas-plan-matrix__row grid border-b border-slate-200 bg-slate-950 text-white"
                    style="
                        --saas-plan-columns: repeat(
                            {{ max(count($items), 1) }},
                            minmax(0, 1fr)
                        );
                    "
                >
                    <div class="p-4 text-sm font-black">
                        {{ __('capell-theme-saas::generic.plan_comparison_label') }}
                    </div>
                    @foreach ($items as $item)
                        <div
                            class="border-t border-white/10 p-4 text-sm font-black md:border-t-0 md:border-l"
                        >
                            {{ $item['title'] ?? __('capell-theme-saas::generic.product_signal') }}
                        </div>
                    @endforeach
                </div>

                @foreach ($featureRows as $feature)
                    <div
                        class="saas-plan-matrix__row grid border-b border-slate-100 last:border-b-0"
                        style="
                            --saas-plan-columns: repeat(
                                {{ max(count($items), 1) }},
                                minmax(0, 1fr)
                            );
                        "
                    >
                        <div
                            class="bg-slate-50 p-4 text-sm font-bold text-slate-700"
                        >
                            {{ $feature }}
                        </div>
                        @foreach ($items as $item)
                            @php
                                $featureValue = $item['features'][$feature] ?? false;
                            @endphp

                            <div
                                class="border-t border-slate-100 p-4 text-sm font-bold text-slate-700 md:border-t-0 md:border-l"
                            >
                                @if ($featureValue === true)
                                    {{ __('capell-theme-saas::generic.included_label') }}
                                @elseif ($featureValue === false || $featureValue === null)
                                    {{ __('capell-theme-saas::generic.not_included_label') }}
                                @else
                                    {{ $featureValue }}
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
