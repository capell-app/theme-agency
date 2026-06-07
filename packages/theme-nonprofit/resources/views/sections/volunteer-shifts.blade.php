@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-nonprofit::generic.volunteer_shifts_label');
    $summary ??= $section->summary ?? (($formBuilderAvailable ?? false) || ($eventsAvailable ?? false) ? __('capell-theme-nonprofit::generic.volunteer_shifts_connected') : __('capell-theme-nonprofit::generic.volunteer_shifts_static'));
@endphp

<section class="theme-section theme-section-volunteer-shifts bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-6 md:grid-cols-[0.8fr_1.2fr]">
            <div>
                <p
                    class="nonprofit-text-primary text-xs font-black tracking-[0.16em] uppercase"
                >
                    {{ __('capell-theme-nonprofit::generic.volunteer_shifts_label') }}
                </p>
                <h2 class="nonprofit-text-ink mt-3 text-4xl font-black">
                    {{ $heading }}
                </h2>
                <p class="mt-4 text-base leading-8 text-slate-600">
                    {{ $summary }}
                </p>
            </div>
            <div class="grid gap-3">
                @forelse ($items as $item)
                    <article
                        class="nonprofit-bg-surface border border-slate-200 p-5"
                    >
                        <p
                            class="nonprofit-text-accent text-xs font-black uppercase"
                        >
                            {{ $item['type'] ?? __('capell-theme-nonprofit::generic.supporter_cta_label') }}
                        </p>
                        <h3 class="nonprofit-text-ink mt-2 text-lg font-black">
                            {{ $item['title'] ?? __('capell-theme-nonprofit::generic.volunteer_shifts_label') }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $item['summary'] ?? __('capell-theme-nonprofit::generic.volunteer_shifts_ready') }}
                        </p>
                    </article>
                @empty
                    <article
                        class="nonprofit-bg-surface border border-dashed border-slate-300 p-6"
                    >
                        <h3 class="nonprofit-text-ink text-lg font-black">
                            {{ __('capell-theme-nonprofit::generic.volunteer_shifts_ready') }}
                        </h3>
                    </article>
                @endforelse
            </div>
        </div>
    </div>
</section>
