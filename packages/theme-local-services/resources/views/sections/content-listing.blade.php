@php
    $listingItems = $items ?? $section->items ?? [];
@endphp

<section class="theme-section theme-section-content-listing bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-5 md:grid-cols-[0.75fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#ea580c] uppercase"
                >
                    {{ __('capell-theme-local-services::generic.service_routes_label') }}
                </p>
                <h2
                    class="mt-4 text-4xl font-black tracking-tight text-[#17211c]"
                >
                    {{ $heading ?? $section->heading }}
                </h2>
            </div>

            @if (($summary ?? $section->summary ?? null) !== null)
                <p
                    class="max-w-2xl text-lg leading-8 text-slate-600 md:justify-self-end"
                >
                    {{ $summary ?? $section->summary }}
                </p>
            @endif
        </div>

        @if ($listingItems === [])
            <div
                class="mt-10 border border-dashed border-[#99f6e4] bg-[#f0fdfa] p-8"
            >
                <p class="text-sm font-black text-[#115e59]">
                    {{ __('capell-theme-local-services::generic.listing_empty_title') }}
                </p>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">
                    {{ __('capell-theme-local-services::generic.listing_empty_summary') }}
                </p>
            </div>
        @else
            <div class="mt-10 grid gap-5">
                @foreach ($listingItems as $index => $item)
                    <article
                        class="group grid overflow-hidden border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-[#0f766e] hover:shadow-xl lg:grid-cols-[0.85fr_1.25fr_0.55fr]"
                    >
                        @if ($item['image'] ?? $item['imageUrl'] ?? null)
                            <img
                                src="{{ $item['image'] ?? $item['imageUrl'] }}"
                                alt=""
                                class="h-full min-h-48 w-full object-cover"
                            />
                        @else
                            <div
                                class="min-h-48 bg-[#134e4a] p-5"
                                aria-hidden="true"
                            >
                                <div
                                    class="flex h-full flex-col justify-between"
                                >
                                    <span class="h-4 w-24 bg-[#f97316]"></span>
                                    <div class="grid grid-cols-3 gap-2">
                                        <span class="h-12 bg-white/20"></span>
                                        <span class="h-12 bg-white/35"></span>
                                        <span class="h-12 bg-white/20"></span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-1 flex-col p-5">
                            <p
                                class="text-xs font-black tracking-[0.16em] text-[#0f766e] uppercase"
                            >
                                {{ $item['type'] ?? __('capell-theme-local-services::generic.route_signal') }}
                            </p>
                            <h3 class="mt-3 text-xl font-black text-[#17211c]">
                                @if ($item['url'] ?? null)
                                    <a
                                        href="{{ $item['url'] }}"
                                        class="hover:text-[#0f766e]"
                                    >
                                        {{ $item['title'] }}
                                    </a>
                                @else
                                    {{ $item['title'] }}
                                @endif
                            </h3>
                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                {{ $item['summary'] ?? $item['description'] ?? '' }}
                            </p>
                            <div
                                class="mt-auto flex flex-wrap gap-2 pt-5 text-xs font-bold"
                            >
                                @foreach (($item['meta'] ?? []) ?: [__('capell-theme-local-services::generic.locality_signal')] as $meta)
                                    <span
                                        class="bg-[#f0fdfa] px-3 py-1 text-[#115e59]"
                                    >
                                        {{ $meta }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div
                            class="border-t border-slate-200 bg-[#f8fafc] p-5 lg:border-t-0 lg:border-l"
                        >
                            <p
                                class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                            >
                                {{ __('capell-theme-local-services::generic.slot_signal') }}
                            </p>
                            <p class="mt-3 text-3xl font-black text-[#ea580c]">
                                {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                            </p>
                            <p class="mt-3 text-sm font-bold text-[#17211c]">
                                {{ __('capell-theme-local-services::generic.availability_signal') }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
