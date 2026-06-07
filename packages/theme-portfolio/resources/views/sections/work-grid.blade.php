@php
    $mediaLibraryAvailable ??= false;
    $projects = $section->items ?? [];
@endphp

<section
    id="work-grid"
    class="theme-section theme-section-work-grid bg-[#f8fafc]"
>
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <div class="grid gap-3 lg:grid-cols-[1fr_1.35fr] lg:items-end">
                <div>
                    <h2
                        class="text-4xl font-black tracking-tight text-[#0f172a]"
                    >
                        {{ $heading }}
                    </h2>
                    <p class="mt-4 max-w-2xl text-lg text-stone-600">
                        @php
                            $connectedCopy = __('capell-theme-portfolio::generic.work_grid_connected');
                            $staticCopy = __('capell-theme-portfolio::generic.work_grid_static');
                        @endphp

                        {{
                            $mediaLibraryAvailable
                            ? ($connectedCopy !== 'capell-theme-portfolio::generic.work_grid_connected' ? $connectedCopy : 'Connected media library')
                            : ($staticCopy !== 'capell-theme-portfolio::generic.work_grid_static' ? $staticCopy : 'Static work grid')
                        }}
                    </p>
                </div>
                <div class="grid grid-cols-3 gap-2 text-sm">
                    <p
                        class="rounded-full border border-slate-200 bg-white px-4 py-2 text-center text-xs font-black tracking-[0.14em] text-slate-500"
                    >
                        {{ __('capell-theme-portfolio::generic.work_grid_projects_stat') }}
                    </p>
                    <p
                        class="rounded-full border border-slate-200 bg-white px-4 py-2 text-center text-xs font-black tracking-[0.14em] text-slate-500"
                    >
                        {{ __('capell-theme-portfolio::generic.work_grid_industries_stat') }}
                    </p>
                    <p
                        class="rounded-full border border-slate-200 bg-white px-4 py-2 text-center text-xs font-black tracking-[0.14em] text-slate-500"
                    >
                        {{ __('capell-theme-portfolio::generic.work_grid_retention_stat') }}
                    </p>
                </div>
            </div>
        </div>
    @endisset

    <div
        class="theme-carousel relative mt-4"
        data-carousel="portfolio-work-grid"
    >
        <div
            class="mx-auto mt-2 flex max-w-5xl [scrollbar-width:none] gap-4 overflow-x-auto px-6 pr-6 pb-2 sm:grid sm:grid-cols-3 [&::-webkit-scrollbar]:hidden"
            data-carousel-track
        >
            @forelse ($projects as $project)
                <article
                    class="min-w-[250px] snap-start rounded-xl border border-slate-200 bg-white p-4"
                >
                    <p
                        class="text-xs font-black tracking-widest text-slate-500 uppercase"
                    >
                        {{ $project['type'] ?? __('capell-theme-portfolio::generic.projects_heading') }}
                    </p>
                    <h3 class="mt-2 text-lg font-black text-[#0f172a]">
                        {{ $project['title'] ?? $project['name'] ?? '' }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ $project['summary'] ?? $project['description'] ?? '' }}
                    </p>
                </article>
            @empty
                <article
                    class="min-w-[250px] snap-start rounded-xl border border-dashed border-slate-300 bg-white p-6 sm:col-span-3"
                >
                    <h3 class="text-lg font-black text-[#0f172a]">
                        {{ __('capell-theme-portfolio::generic.premium_layout_ready') }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ __('capell-theme-portfolio::generic.premium_layout_empty') }}
                    </p>
                </article>
            @endforelse
        </div>

        <button
            type="button"
            class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-slate-200 bg-white p-2 text-sm font-semibold shadow-md"
            aria-label="{{ __('capell-theme-portfolio::generic.carousel_previous') }}"
            data-carousel-prev
        >
            ‹
        </button>
        <button
            type="button"
            class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-slate-200 bg-white p-2 text-sm font-semibold shadow-md"
            aria-label="{{ __('capell-theme-portfolio::generic.carousel_next') }}"
            data-carousel-next
        >
            ›
        </button>
    </div>
</section>
