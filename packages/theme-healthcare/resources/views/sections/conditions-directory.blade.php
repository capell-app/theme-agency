@php
    $items = $section->items ?? $section->conditions ?? $section->treatments ?? [];
    $heading = $section->heading ?? __('capell-theme-healthcare::generic.conditions_heading');
    $summary = $section->summary ?? __('capell-theme-healthcare::generic.conditions_summary');
@endphp

<section class="healthcare-conditions-directory bg-white">
    <div class="grid gap-8 px-6 lg:grid-cols-[0.7fr_1.3fr] lg:items-start">
        <div>
            <p
                class="text-xs font-black tracking-widest text-[var(--healthcare-primary)] uppercase"
            >
                {{ __('capell-theme-healthcare::generic.conditions_label') }}
            </p>
            <h2
                class="mt-3 text-4xl font-black tracking-tight text-[var(--healthcare-ink)]"
            >
                {{ $heading }}
            </h2>
            @if ($summary)
                <p class="mt-4 max-w-xl text-lg text-stone-600">
                    {{ $summary }}
                </p>
            @endif
        </div>

        <div class="grid gap-3 md:grid-cols-2">
            @forelse ($items as $item)
                @php
                    $links = $item['links'] ?? $item['services'] ?? [];
                @endphp

                <article
                    class="rounded-xl border border-[var(--healthcare-line)] bg-[var(--healthcare-surface)] p-5"
                >
                    <p
                        class="text-xs font-black tracking-widest text-[var(--healthcare-link)] uppercase"
                    >
                        {{ $item['type'] ?? __('capell-theme-healthcare::generic.condition_label') }}
                    </p>
                    <h3
                        class="mt-3 text-xl font-black text-[var(--healthcare-ink)]"
                    >
                        {{ $item['title'] ?? $item['label'] ?? '' }}
                    </h3>
                    @if (! empty($item['summary']))
                        <p class="mt-2 text-sm text-stone-600">
                            {{ $item['summary'] }}
                        </p>
                    @endif

                    @if ($links !== [])
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($links as $link)
                                <a
                                    href="{{ is_array($link) ? $link['url'] ?? '#' : '#' }}"
                                    class="rounded-full bg-white px-3 py-1 text-xs font-black text-[var(--healthcare-primary)]"
                                >
                                    {{ is_array($link) ? $link['label'] ?? $link['title'] ?? '' : $link }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </article>
            @empty
                <article
                    class="rounded-xl border border-dashed border-[var(--healthcare-line)] bg-[var(--healthcare-surface)] p-6"
                >
                    <p
                        class="text-xs font-black text-[var(--healthcare-primary)] uppercase"
                    >
                        {{ __('capell-theme-healthcare::generic.conditions_label') }}
                    </p>
                    <h3
                        class="mt-3 text-lg font-black text-[var(--healthcare-ink)]"
                    >
                        {{ __('capell-theme-healthcare::generic.conditions_empty_title') }}
                    </h3>
                    <p class="mt-2 text-sm text-stone-600">
                        {{ __('capell-theme-healthcare::generic.conditions_empty_summary') }}
                    </p>
                </article>
            @endforelse
        </div>
    </div>
</section>
