@php
    $listingItems = $items ?? $section->items ?? [];
@endphp

<section class="theme-section theme-section-content-listing bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-5 md:grid-cols-[0.8fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#b45309] uppercase"
                >
                    {{ __('capell-theme-nonprofit::generic.story_cards_label') }}
                </p>
                <h2
                    class="mt-4 text-4xl font-black tracking-tight text-[#132014]"
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
                class="mt-10 border border-dashed border-[#bbf7d0] bg-[#f7fbf4] p-8"
            >
                <p class="text-sm font-black text-[#166534]">
                    {{ __('capell-theme-nonprofit::generic.listing_empty_title') }}
                </p>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">
                    {{ __('capell-theme-nonprofit::generic.listing_empty_summary') }}
                </p>
            </div>
        @else
            <div class="mt-10 grid gap-5 md:grid-cols-2">
                @foreach ($listingItems as $item)
                    <article
                        class="group grid overflow-hidden border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-[#166534] hover:shadow-xl md:grid-cols-[0.9fr_1fr]"
                    >
                        @if ($item['image'] ?? $item['imageUrl'] ?? null)
                            <img
                                src="{{ $item['image'] ?? $item['imageUrl'] }}"
                                alt=""
                                class="h-full min-h-56 w-full object-cover"
                            />
                        @else
                            <div
                                class="min-h-56 bg-[#12351f] p-4"
                                aria-hidden="true"
                            >
                                <div
                                    class="flex h-full items-end justify-between"
                                >
                                    <span
                                        class="h-24 w-24 rounded-full bg-[#facc15]"
                                    ></span>
                                    <span
                                        class="h-16 w-16 rounded-full bg-white/20"
                                    ></span>
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-1 flex-col p-5">
                            <p
                                class="text-xs font-black tracking-[0.16em] text-[#166534] uppercase"
                            >
                                {{ $item['type'] ?? __('capell-theme-nonprofit::generic.story_signal') }}
                            </p>
                            <h3 class="mt-3 text-lg font-black text-[#0f172a]">
                                @if ($item['url'] ?? null)
                                    <a
                                        href="{{ $item['url'] }}"
                                        class="hover:text-[#166534]"
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
                                @foreach (($item['meta'] ?? []) ?: [__('capell-theme-nonprofit::generic.story_meta_signal')] as $meta)
                                    <span
                                        class="bg-[#fef3c7] px-3 py-1 text-[#92400e]"
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
