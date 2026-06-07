@php
    $mediaKitCards = $section->items ?? [];
    $label ??= $section->label ?? __('capell-theme-portfolio::generic.speaking_media_kit_label');
    $summary ??= $section->summary ?? __('capell-theme-portfolio::generic.speaking_media_kit_summary');
@endphp

<section
    class="theme-section theme-section-speaking-media-kit portfolio-bg-card-soft"
>
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <p class="text-xs font-black tracking-[0.16em] text-slate-500">
                {{ $label }}
            </p>
            <h2 class="portfolio-text-ink text-4xl font-black tracking-tight">
                {{ $heading }}
            </h2>
            @if ($summary)
                <p class="mt-4 max-w-2xl text-stone-600">
                    {{ $summary }}
                </p>
            @endif

            <div class="mt-7 grid gap-3 sm:grid-cols-3">
                @forelse ($mediaKitCards as $card)
                    <article
                        class="rounded-xl border border-slate-200 bg-white p-4"
                    >
                        <h3 class="text-sm font-black text-slate-500 uppercase">
                            {{ $card['title'] ?? $card['name'] ?? __('capell-theme-portfolio::generic.media_kit_label') }}
                        </h3>
                        <p class="mt-2 text-sm text-stone-600">
                            {{ $card['summary'] ?? $card['description'] ?? '' }}
                        </p>
                    </article>
                @empty
                    <article
                        class="rounded-xl border border-dashed border-slate-300 bg-white p-6 sm:col-span-3"
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
        </div>
    @endisset
</section>
