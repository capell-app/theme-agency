@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-healthcare::generic.locations_label');
    $summary ??= $section->summary ?? null;
@endphp

<section class="theme-section theme-section-locations bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-6 md:grid-cols-[0.75fr_1fr]">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[var(--healthcare-primary)] uppercase"
                >
                    {{ __('capell-theme-healthcare::generic.locations_label') }}
                </p>
                <h2
                    class="mt-3 text-4xl font-black text-[var(--healthcare-ink)]"
                >
                    {{ $heading }}
                </h2>
                @if ($summary)
                    <p class="mt-4 text-base leading-8 text-slate-600">
                        {{ $summary }}
                    </p>
                @endif
            </div>

            <div class="grid gap-4">
                @forelse ($items as $item)
                    <article
                        class="grid gap-2 border border-[var(--healthcare-line)] bg-[var(--healthcare-surface)] p-5"
                    >
                        <p
                            class="text-xs font-black text-[var(--healthcare-primary)] uppercase"
                        >
                            {{ $item['type'] ?? __('capell-theme-healthcare::generic.location') }}
                        </p>
                        <h3
                            class="text-xl font-black text-[var(--healthcare-ink)]"
                        >
                            {{ $item['title'] ?? __('capell-theme-healthcare::generic.location') }}
                        </h3>
                        <p class="text-sm leading-6 text-slate-600">
                            {{ $item['summary'] ?? __('capell-theme-healthcare::generic.locations_ready') }}
                        </p>
                        @if ($item['address'] ?? null)
                            <p
                                class="mt-2 text-xs font-black text-[var(--healthcare-ink)] uppercase"
                            >
                                {{ __('capell-theme-healthcare::generic.location_address') }}
                            </p>
                            <p class="text-sm leading-6 text-slate-600">
                                {{ $item['address'] }}
                            </p>
                        @endif

                        @if ($item['hours'] ?? $item['openingHours'] ?? null)
                            <p
                                class="mt-2 text-xs font-black text-[var(--healthcare-ink)] uppercase"
                            >
                                {{ __('capell-theme-healthcare::generic.location_hours') }}
                            </p>
                            <p class="text-sm leading-6 text-slate-600">
                                {{ $item['hours'] ?? $item['openingHours'] }}
                            </p>
                        @endif

                        <div class="mt-3 flex flex-wrap gap-3">
                            @if ($item['phone'] ?? null)
                                <a
                                    href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $item['phone']) }}"
                                    class="text-sm font-black text-[var(--healthcare-primary)]"
                                >
                                    {{ __('capell-theme-healthcare::generic.location_phone') }}
                                </a>
                            @endif

                            @if ($item['mapUrl'] ?? null)
                                <a
                                    href="{{ $item['mapUrl'] }}"
                                    class="text-sm font-black text-[var(--healthcare-link)]"
                                >
                                    {{ __('capell-theme-healthcare::generic.location_map') }}
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    <article
                        class="border border-dashed border-[var(--healthcare-line)] bg-[var(--healthcare-surface)] p-6"
                    >
                        <h3
                            class="text-lg font-black text-[var(--healthcare-ink)]"
                        >
                            {{ __('capell-theme-healthcare::generic.locations_ready') }}
                        </h3>
                    </article>
                @endforelse
            </div>
        </div>
    </div>
</section>
