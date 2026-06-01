<section
    class="theme-section theme-section-testimonials bg-[#f4f6f8] px-6 py-16"
>
    <div class="mx-auto max-w-5xl">
        @isset($heading)
            <h2 class="text-4xl font-black tracking-tight text-[#0f172a]">
                {{ $heading }}
            </h2>
        @endisset

        <p class="mt-4 max-w-2xl text-stone-600">
            Client stories with measurable outcomes, visual direction, and clear
            outcomes.
        </p>
    </div>

    <div
        class="theme-carousel relative mt-10"
        data-carousel="portfolio-testimonials"
    >
        <div
            class="mx-auto flex max-w-5xl snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 [&::-webkit-scrollbar]:hidden"
            data-carousel-track
        >
            <article
                class="min-w-[280px] snap-start rounded-2xl border border-slate-200 bg-white p-5"
            >
                <p class="text-sm text-stone-700">
                    “Great clarity, premium design execution, and measurable
                    uplift.”
                </p>
                <p
                    class="mt-4 text-xs font-black tracking-[0.16em] text-slate-500"
                >
                    Client Snapshot
                </p>
            </article>
            <article
                class="min-w-[280px] snap-start rounded-2xl border border-slate-200 bg-white p-5 md:min-w-0"
            >
                <p class="text-sm text-stone-700">
                    “A refined look and feel that reflects our brand promise
                    from first screen.”
                </p>
                <p
                    class="mt-4 text-xs font-black tracking-[0.16em] text-slate-500"
                >
                    Creative Partner
                </p>
            </article>
            <article
                class="min-w-[280px] snap-start rounded-2xl border border-slate-200 bg-white p-5 md:min-w-0"
            >
                <p class="text-sm text-stone-700">
                    “The layout is polished, fast, and conversion-minded.”
                </p>
                <p
                    class="mt-4 text-xs font-black tracking-[0.16em] text-slate-500"
                >
                    Design Lead
                </p>
            </article>
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
