@php
    $heading ??= $section->heading ?? __('capell-theme-portfolio::generic.about_bio_heading');
    $summary ??= $section->summary ?? __('capell-theme-portfolio::generic.about_bio_summary');
    $items = $section->items ?? [];

    if ($items === []) {
        $items = __('capell-theme-portfolio::generic.about_bio_defaults');
    }
@endphp

<section class="theme-section theme-section-about-bio bg-white">
    <div
        class="mx-auto grid max-w-6xl gap-8 px-6 py-16 lg:grid-cols-[0.8fr_1.2fr] lg:items-start"
    >
        <div>
            <p
                class="portfolio-text-primary text-xs font-black tracking-[0.16em] uppercase"
            >
                {{ __('capell-theme-portfolio::generic.about_bio_label') }}
            </p>
            <h2 class="portfolio-text-ink mt-3 text-4xl font-black">
                {{ $heading }}
            </h2>
            @if ($summary)
                <p class="mt-4 text-base leading-8 text-slate-600">
                    {{ $summary }}
                </p>
            @endif
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            @foreach ($items as $item)
                <article
                    class="portfolio-bg-card-soft border border-slate-200 p-5"
                >
                    <p
                        class="portfolio-text-primary-strong text-xs font-black uppercase"
                    >
                        {{ $item['type'] ?? __('capell-theme-portfolio::generic.about_bio_card_label') }}
                    </p>
                    <h3 class="portfolio-text-ink mt-2 text-lg font-black">
                        {{ $item['title'] ?? $item['name'] ?? '' }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ $item['summary'] ?? $item['description'] ?? '' }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
