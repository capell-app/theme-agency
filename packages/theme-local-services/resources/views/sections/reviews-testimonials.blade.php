@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-local-services::generic.reviews_label');
    $summary ??= $section->summary ?? __('capell-theme-local-services::generic.reviews_summary');
@endphp

<section class="theme-section theme-section-reviews-testimonials bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#0f766e] uppercase"
                >
                    {{ __('capell-theme-local-services::generic.reviews_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black text-[#13231f]">
                    {{ $heading }}
                </h2>
            </div>

            @if ($summary)
                <p class="text-base leading-8 text-slate-600">
                    {{ $summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @forelse ($items as $item)
                <figure class="border border-slate-200 bg-[#f7fbf8] p-5">
                    <div
                        class="flex gap-1 text-sm font-black text-[#f97316]"
                        aria-label="{{ __('capell-theme-local-services::generic.review_rating_label', ['rating' => $item['rating'] ?? 5]) }}"
                    >
                        @for ($star = 1; $star <= 5; $star++)
                            <span aria-hidden="true">★</span>
                        @endfor
                    </div>
                    <blockquote class="mt-4 text-sm leading-7 text-slate-700">
                        {{ $item['quote'] ?? $item['summary'] ?? __('capell-theme-local-services::generic.review_ready') }}
                    </blockquote>
                    <figcaption
                        class="mt-4 text-xs font-black tracking-[0.16em] text-[#0f766e] uppercase"
                    >
                        {{ $item['name'] ?? $item['title'] ?? __('capell-theme-local-services::generic.review_customer_label') }}
                    </figcaption>
                </figure>
            @empty
                <article
                    class="border border-dashed border-slate-300 bg-[#f7fbf8] p-6 md:col-span-3"
                >
                    <h3 class="text-lg font-black text-[#13231f]">
                        {{ __('capell-theme-local-services::generic.premium_layout_ready') }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ __('capell-theme-local-services::generic.premium_layout_empty') }}
                    </p>
                </article>
            @endforelse
        </div>
    </div>
</section>
