@php
    $listingItems = $items ?? $section->items ?? [];
@endphp

<section class="theme-section theme-section-content-listing bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-5 md:grid-cols-[0.75fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#f59e0b] uppercase"
                >
                    {{ __('capell-theme-knowledge::generic.archive_label') }}
                </p>
                <h2
                    class="mt-4 text-4xl font-black tracking-tight text-[#111827]"
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
                class="mt-10 border border-dashed border-[#bfdbfe] bg-[#eff6ff] p-8"
            >
                <p class="text-sm font-black text-[#1e40af]">
                    {{ __('capell-theme-knowledge::generic.listing_empty_title') }}
                </p>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">
                    {{ __('capell-theme-knowledge::generic.listing_empty_summary') }}
                </p>
            </div>
        @else
            <div class="mt-10 grid gap-5">
                @foreach ($listingItems as $index => $item)
                    <article
                        class="group grid overflow-hidden border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-[#1d4ed8] hover:shadow-xl lg:grid-cols-[0.72fr_1.35fr_0.62fr]"
                    >
                        @if ($item['image'] ?? $item['imageUrl'] ?? null)
                            <img
                                src="{{ $item['image'] ?? $item['imageUrl'] }}"
                                alt=""
                                class="h-full min-h-48 w-full object-cover"
                            />
                        @else
                            <div
                                class="min-h-48 bg-[#1e3a8a] p-5"
                                aria-hidden="true"
                            >
                                <span
                                    class="block h-3 w-20 bg-[#f59e0b]"
                                ></span>
                                <div class="mt-10 space-y-2">
                                    <span
                                        class="block h-2 w-full bg-white/35"
                                    ></span>
                                    <span
                                        class="block h-2 w-5/6 bg-white/25"
                                    ></span>
                                    <span
                                        class="block h-2 w-2/3 bg-white/25"
                                    ></span>
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-1 flex-col p-5">
                            <p
                                class="text-xs font-black tracking-[0.16em] text-[#1d4ed8] uppercase"
                            >
                                {{ $item['type'] ?? __('capell-theme-knowledge::generic.article_signal') }}
                            </p>
                            <h3 class="mt-3 text-xl font-black text-[#111827]">
                                @if ($item['url'] ?? null)
                                    <a
                                        href="{{ $item['url'] }}"
                                        class="hover:text-[#1d4ed8]"
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
                                @foreach (($item['meta'] ?? []) ?: [__('capell-theme-knowledge::generic.library_signal')] as $meta)
                                    <span
                                        class="bg-[#eff6ff] px-3 py-1 text-[#1e40af]"
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
                                {{ __('capell-theme-knowledge::generic.reading_queue_label') }}
                            </p>
                            <p
                                class="mt-3 font-mono text-3xl font-black text-[#f59e0b]"
                            >
                                {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                            </p>
                            <p class="mt-3 text-sm font-bold text-[#111827]">
                                {{ __('capell-theme-knowledge::generic.saved_signal') }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
