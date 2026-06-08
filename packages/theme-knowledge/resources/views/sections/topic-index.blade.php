@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-knowledge::generic.topic_index_label');
    $summary ??= $section->summary ?? null;
@endphp

<section
    class="theme-section theme-section-topic-index bg-[var(--site-theme-surface)]"
>
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[var(--site-theme-primary)] uppercase"
                >
                    {{ __('capell-theme-knowledge::generic.topic_index_label') }}
                </p>
                <h2
                    class="mt-3 text-4xl font-black text-[var(--site-theme-heading)]"
                >
                    {{ $heading }}
                </h2>
            </div>
            @if ($summary)
                <p class="text-base leading-8 text-slate-600">
                    {{ $summary }}
                </p>
            @endif
        </div>
        <div class="mt-10 grid gap-3 md:grid-cols-4">
            @forelse ($items as $item)
                <article class="border border-slate-200 bg-white p-4">
                    <p
                        class="text-xs font-black text-[var(--site-theme-accent)] uppercase"
                    >
                        {{ $item['type'] ?? __('capell-theme-knowledge::generic.topic_signal') }}
                    </p>
                    <h3
                        class="mt-2 text-lg font-black text-[var(--site-theme-heading)]"
                    >
                        {{ $item['title'] ?? __('capell-theme-knowledge::generic.topic_hubs_label') }}
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ $item['summary'] ?? __('capell-theme-knowledge::generic.topic_index_ready') }}
                    </p>
                </article>
            @empty
                <article
                    class="border border-dashed border-slate-300 bg-white p-6"
                >
                    <h3
                        class="text-lg font-black text-[var(--site-theme-heading)]"
                    >
                        {{ __('capell-theme-knowledge::generic.topic_index_ready') }}
                    </h3>
                </article>
            @endforelse
        </div>
    </div>
</section>
