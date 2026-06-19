@php
    $hours ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-dog-walkers::generic.opening_hours_label');
    $summary ??= $section->summary ?? __('capell-theme-dog-walkers::generic.opening_hours_summary');
    $openNow = $section->openNow ?? null;
@endphp

<section class="theme-section theme-section-opening-hours bg-[#f7fbf8]">
    <div
        class="mx-auto grid max-w-6xl gap-8 px-6 py-16 md:grid-cols-[0.72fr_1.28fr]"
    >
        <div>
            <p
                class="text-xs font-black tracking-[0.16em] text-[#0f766e] uppercase"
            >
                {{ __('capell-theme-dog-walkers::generic.opening_hours_label') }}
            </p>
            <h2 class="mt-3 text-4xl font-black text-[#13231f]">
                {{ $heading }}
            </h2>
            @if ($summary)
                <p class="mt-4 text-base leading-8 text-slate-600">
                    {{ $summary }}
                </p>
            @endif

            @if (is_bool($openNow))
                <p
                    class="mt-5 inline-flex bg-white px-4 py-2 text-sm font-black text-[#0f766e]"
                >
                    {{ $openNow ? __('capell-theme-dog-walkers::generic.open_now') : __('capell-theme-dog-walkers::generic.closed_now') }}
                </p>
            @endif
        </div>

        <div class="grid gap-3">
            @forelse ($hours as $hour)
                <article
                    class="grid gap-2 border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_auto] sm:items-center"
                >
                    <h3 class="text-base font-black text-[#13231f]">
                        {{ $hour['day'] ?? $hour['label'] ?? __('capell-theme-dog-walkers::generic.opening_hours_day_label') }}
                    </h3>
                    <p class="text-sm font-bold text-slate-600">
                        {{ $hour['hours'] ?? (($hour['opens'] ?? '') . (($hour['opens'] ?? '') !== '' && ($hour['closes'] ?? '') !== '' ? ' - ' : '') . ($hour['closes'] ?? '')) ?: __('capell-theme-dog-walkers::generic.opening_hours_by_request') }}
                    </p>
                </article>
            @empty
                <article
                    class="border border-dashed border-slate-300 bg-white p-6"
                >
                    <h3 class="text-lg font-black text-[#13231f]">
                        {{ __('capell-theme-dog-walkers::generic.premium_layout_ready') }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ __('capell-theme-dog-walkers::generic.premium_layout_empty') }}
                    </p>
                </article>
            @endforelse
        </div>
    </div>
</section>
