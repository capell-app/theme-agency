@php
    $items = $section->items ?? [];
@endphp

<section
    class="theme-section theme-section-content-listing portfolio-bg-card-soft"
>
    <div class="mx-auto max-w-6xl px-6 py-16 lg:py-20">
        <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p
                    class="portfolio-text-secondary text-xs font-black uppercase"
                >
                    {{ __('capell-theme-portfolio::generic.work_index_label') }}
                </p>
                <h2
                    class="portfolio-text-ink mt-4 text-4xl font-black tracking-tight"
                >
                    {{ $section->heading }}
                </h2>
            </div>

            @if ($section->summary ?? null)
                <p class="max-w-2xl text-base leading-7 text-slate-600">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        @if ($items !== [])
            <div class="mt-10 grid gap-5 md:grid-cols-6">
                @foreach ($items as $item)
                    @php
                        $image = $item['image'] ?? $item['imageUrl'] ?? $item['mediaUrl'] ?? null;
                        $imageAlt = $item['imageAlt'] ?? $item['mediaAlt'] ?? $item['alt'] ?? $item['title'] ?? $item['name'] ?? __('capell-theme-portfolio::generic.project_image_alt');
                    @endphp

                    <a
                        href="{{ $item['url'] ?? '#' }}"
                        class="{{ $loop->first ? 'md:col-span-4 md:row-span-2' : 'md:col-span-2' }} group portfolio-hover-border-secondary grid min-h-full overflow-hidden border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl"
                    >
                        @if (is_string($image) && $image !== '')
                            <img
                                src="{{ $image }}"
                                alt="{{ $imageAlt }}"
                                width="{{ $loop->first ? 1280 : 640 }}"
                                height="{{ $loop->first ? 640 : 432 }}"
                                loading="lazy"
                                decoding="async"
                                class="{{ $loop->first ? 'aspect-[16/8]' : 'aspect-[4/2.7]' }} w-full object-cover transition duration-300 group-hover:scale-[1.025]"
                            />
                        @else
                            <span
                                class="{{ $loop->first ? 'aspect-[16/8]' : 'aspect-[4/2.7]' }} portfolio-bg-deep flex items-end p-5"
                                aria-hidden="true"
                            >
                                <span class="block w-full">
                                    <span
                                        class="portfolio-bg-highlight block h-2 w-24"
                                    ></span>
                                    <span class="mt-10 grid grid-cols-3 gap-2">
                                        <span class="h-9 bg-white/20"></span>
                                        <span
                                            class="portfolio-bg-secondary h-9"
                                        ></span>
                                        <span class="h-9 bg-white/10"></span>
                                    </span>
                                </span>
                            </span>
                        @endif

                        <span class="grid gap-4 p-5 sm:p-6">
                            <span
                                class="flex items-start justify-between gap-3"
                            >
                                <span
                                    class="portfolio-text-secondary text-xs font-black uppercase"
                                >
                                    {{ $item['type'] ?? __('capell-theme-portfolio::generic.project_label') }}
                                </span>
                                <span
                                    class="font-mono text-sm font-black text-slate-400"
                                >
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </span>

                            <span
                                class="{{ $loop->first ? 'text-2xl' : 'text-xl' }} portfolio-text-ink block font-black"
                            >
                                {{ $item['title'] ?? $item['name'] ?? '' }}
                            </span>

                            @if (! empty($item['summary']) || ! empty($item['description']))
                                <span
                                    class="block text-sm leading-6 text-slate-600"
                                >
                                    {{ $item['summary'] ?? $item['description'] }}
                                </span>
                            @endif

                            <span
                                class="grid grid-cols-[1fr_auto] items-end gap-4 border-t border-slate-200 pt-4"
                            >
                                <span>
                                    <span
                                        class="portfolio-text-primary-strong block text-xs font-black uppercase"
                                    >
                                        {{ __('capell-theme-portfolio::generic.outcome_label') }}
                                    </span>
                                    <span
                                        class="portfolio-text-ink mt-1 block text-sm font-black"
                                    >
                                        {{ __('capell-theme-portfolio::generic.case_file_summary') }}
                                    </span>
                                </span>
                                <span
                                    class="portfolio-bg-ink inline-flex px-3 py-1.5 text-xs font-black text-white"
                                >
                                    {{ __('capell-theme-portfolio::generic.view_case_label') }}
                                </span>
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <div
                class="mt-10 border border-dashed border-slate-300 bg-white p-8 text-slate-600"
            >
                {{ __('capell-theme-portfolio::generic.empty_work_index') }}
            </div>
        @endif
    </div>
</section>
