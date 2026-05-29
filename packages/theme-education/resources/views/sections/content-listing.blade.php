@php
    $listingItems = $items ?? $section->items ?? [];
@endphp

<section class="theme-section theme-section-content-listing bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-5 md:grid-cols-[0.72fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#14b8a6] uppercase"
                >
                    {{ __('capell-theme-education::generic.learning_resources_label') }}
                </p>
                <h2
                    class="mt-4 text-4xl font-black tracking-tight text-[#0f172a]"
                >
                    {{ $heading ?? $section->heading }}
                </h2>
            </div>

            @if (($summary ?? $section->summary ?? null) !== null)
                <p class="max-w-2xl text-lg text-slate-600 md:justify-self-end">
                    {{ $summary ?? $section->summary }}
                </p>
            @endif
        </div>

        @if ($listingItems === [])
            <div
                class="mt-10 border border-dashed border-[#c7d2fe] bg-[#f8fbff] p-8"
            >
                <p class="text-sm font-black text-[#4338ca]">
                    {{ __('capell-theme-education::generic.listing_empty_title') }}
                </p>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">
                    {{ __('capell-theme-education::generic.listing_empty_summary') }}
                </p>
            </div>
        @else
            <div class="mt-10 grid gap-4 md:grid-cols-3">
                @foreach ($listingItems as $item)
                    <article
                        class="group flex min-h-full flex-col overflow-hidden border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-[#4338ca] hover:shadow-xl"
                    >
                        @if ($item['image'] ?? $item['imageUrl'] ?? null)
                            <img
                                src="{{ $item['image'] ?? $item['imageUrl'] }}"
                                alt=""
                                class="aspect-[4/2.4] w-full object-cover"
                            />
                        @else
                            <div
                                class="aspect-[4/2.4] bg-[#eef6ff] p-4"
                                aria-hidden="true"
                            >
                                <div class="grid h-full grid-cols-3 gap-2">
                                    <span class="bg-white"></span>
                                    <span class="bg-[#4338ca]/20"></span>
                                    <span class="bg-white"></span>
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-1 flex-col p-5">
                            <p
                                class="text-xs font-black tracking-[0.16em] text-[#4338ca] uppercase"
                            >
                                {{ $item['type'] ?? __('capell-theme-education::generic.resource_signal') }}
                            </p>
                            <h3 class="mt-3 text-lg font-black text-[#0f172a]">
                                @if ($item['url'] ?? null)
                                    <a
                                        href="{{ $item['url'] }}"
                                        class="hover:text-[#4338ca]"
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
                                class="mt-5 flex flex-wrap gap-2 text-xs font-bold"
                            >
                                @foreach (($item['meta'] ?? []) ?: [__('capell-theme-education::generic.resource_meta_signal')] as $meta)
                                    <span
                                        class="bg-[#eef2ff] px-3 py-1 text-[#4338ca]"
                                    >
                                        {{ $meta }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
