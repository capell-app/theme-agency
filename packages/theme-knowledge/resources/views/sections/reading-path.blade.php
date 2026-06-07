@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-knowledge::generic.reading_path_label');
    $summary ??= $section->summary ?? null;
@endphp

<section class="theme-section theme-section-reading-path bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[var(--site-theme-primary)] uppercase"
                >
                    {{ __('capell-theme-knowledge::generic.reading_path_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black text-[var(--site-theme-heading)]">
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
                <article class="border border-slate-200 bg-[var(--site-theme-surface)] p-5">
                    <p class="text-xs font-black text-[var(--site-theme-accent)] uppercase">
                        {{ $item['type'] ?? __('capell-theme-knowledge::generic.reading_signal') }}
                    </p>
                    <h3 class="mt-3 text-xl font-black text-[var(--site-theme-heading)]">
                        {{ $item['title'] ?? __('capell-theme-knowledge::generic.library_signal') }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ $item['summary'] ?? __('capell-theme-knowledge::generic.reading_path_ready') }}
                    </p>
                </article>
            @empty
                <article
                    class="border border-dashed border-slate-300 bg-[var(--site-theme-surface)] p-6"
                >
                    <h3 class="text-lg font-black text-[var(--site-theme-heading)]">
                        {{ __('capell-theme-knowledge::generic.premium_layout_ready') }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ __('capell-theme-knowledge::generic.premium_layout_empty') }}
                    </p>
                </article>
            @endforelse
        </div>
    </div>
</section>
