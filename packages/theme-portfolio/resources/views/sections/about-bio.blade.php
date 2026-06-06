@php
    $heading ??= $section->heading ?? __('capell-theme-portfolio::generic.about_bio_heading');
    $summary ??= $section->summary ?? __('capell-theme-portfolio::generic.about_bio_summary');
    $items = $section->items ?? [];

    if ($items === []) {
        $items = __('capell-theme-portfolio::generic.about_bio_defaults');
    }
@endphp

<section class="theme-section theme-section-about-bio bg-white">
    <div class="mx-auto grid max-w-6xl gap-8 px-6 py-16 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
        <div>
            <p class="text-xs font-black tracking-[0.16em] text-[#7c2d12] uppercase">
                {{ __('capell-theme-portfolio::generic.about_bio_label') }}
            </p>
            <h2 class="mt-3 text-4xl font-black text-[#0f172a]">
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
                <article class="border border-slate-200 bg-[#f8fafc] p-5">
                    <p class="text-xs font-black text-[#9a3412] uppercase">
                        {{ $item['type'] ?? __('capell-theme-portfolio::generic.about_bio_card_label') }}
                    </p>
                    <h3 class="mt-2 text-lg font-black text-[#0f172a]">
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
