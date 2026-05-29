@php
    $listingItems = $items ?? $section->items ?? [];
@endphp

<section
    class="education-resource-section theme-section theme-section-content-listing bg-white"
>
    <div class="mx-auto max-w-6xl px-6 py-16 lg:py-20">
        <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p class="text-xs font-black text-[#0f766e] uppercase">
                    {{ __('capell-theme-education::generic.learning_resources_label') }}
                </p>
                <h2
                    class="mt-4 text-4xl font-black tracking-tight text-[#0f172a]"
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
                class="education-empty-state mt-10 border border-dashed border-[#c7d2fe] bg-[#f8fbff] p-8"
            >
                <p class="text-sm font-black text-[#4338ca]">
                    {{ __('capell-theme-education::generic.listing_empty_title') }}
                </p>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">
                    {{ __('capell-theme-education::generic.listing_empty_summary') }}
                </p>
            </div>
        @else
            <div class="mt-10 grid gap-5 md:grid-cols-6">
                @foreach ($listingItems as $item)
                    @php
                        $image = $item['image'] ?? $item['imageUrl'] ?? $item['mediaUrl'] ?? null;
                    @endphp

                    <article
                        class="education-resource-card {{ $loop->first ? 'md:col-span-3 md:row-span-2' : 'md:col-span-3 lg:col-span-2' }} group grid min-h-full overflow-hidden border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-[#4338ca] hover:shadow-xl"
                    >
                        @if (is_string($image) && $image !== '')
                            <img
                                src="{{ $image }}"
                                alt=""
                                class="{{ $loop->first ? 'aspect-[16/8]' : 'aspect-[4/2.5]' }} w-full object-cover"
                            />
                        @else
                            <div
                                class="{{ $loop->first ? 'aspect-[16/8]' : 'aspect-[4/2.5]' }} bg-[#eef6ff] p-4"
                                aria-hidden="true"
                            >
                                <div class="grid h-full grid-cols-3 gap-2">
                                    <span class="bg-white"></span>
                                    <span class="bg-[#4338ca]/20"></span>
                                    <span class="bg-[#14b8a6]/25"></span>
                                </div>
                            </div>
                        @endif

                        <div class="grid gap-4 p-5">
                            <div class="flex items-start justify-between gap-3">
                                <p
                                    class="text-xs font-black text-[#4338ca] uppercase"
                                >
                                    {{ $item['type'] ?? __('capell-theme-education::generic.resource_signal') }}
                                </p>
                                <p
                                    class="font-mono text-sm font-black text-slate-400"
                                >
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </p>
                            </div>

                            <h3 class="text-xl font-black text-[#0f172a]">
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
                            <p class="text-sm leading-6 text-slate-600">
                                {{ $item['summary'] ?? $item['description'] ?? '' }}
                            </p>
                            <div class="flex flex-wrap gap-2 text-xs font-bold">
                                @foreach (($item['meta'] ?? []) ?: [__('capell-theme-education::generic.resource_meta_signal'), __('capell-theme-education::generic.course_outcome_signal')] as $meta)
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
