@php
    $testimonials = $section->items ?? [];
    $summary ??= $section->summary ?? __('capell-theme-portfolio::generic.testimonials_summary');
@endphp

<section
    class="theme-section theme-section-testimonials portfolio-bg-card-soft px-6 py-16"
>
    <div class="mx-auto max-w-5xl">
        @isset($heading)
            <h2 class="portfolio-text-ink text-4xl font-black tracking-tight">
                {{ $heading }}
            </h2>
        @endisset

        @if ($summary)
            <p class="mt-4 max-w-2xl text-stone-600">
                {{ $summary }}
            </p>
        @endif
    </div>

    <div
        class="theme-carousel relative mt-10"
        data-carousel="portfolio-testimonials"
    >
        <div
            class="mx-auto flex max-w-5xl snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 [&::-webkit-scrollbar]:hidden"
            data-carousel-track
        >
            @forelse ($testimonials as $testimonial)
                <article
                    class="min-w-[280px] snap-start rounded-2xl border border-slate-200 bg-white p-5 md:min-w-0"
                >
                    <p class="text-sm text-stone-700">
                        {{ $testimonial['quote'] ?? $testimonial['summary'] ?? $testimonial['description'] ?? '' }}
                    </p>
                    <p
                        class="mt-4 text-xs font-black tracking-[0.16em] text-slate-500"
                    >
                        {{ $testimonial['attribution'] ?? $testimonial['name'] ?? $testimonial['title'] ?? __('capell-theme-portfolio::generic.testimonial_attribution_label') }}
                    </p>
                </article>
            @empty
                <article
                    class="min-w-[280px] snap-start rounded-2xl border border-dashed border-slate-300 bg-white p-6 md:col-span-3 md:min-w-0"
                >
                    <h3 class="portfolio-text-ink text-lg font-black">
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
