@php
    $items = $section->items ?? $section->steps ?? [];
    $heading = $section->heading ?? __('capell-theme-healthcare::generic.emergency_heading');
    $summary = $section->summary ?? __('capell-theme-healthcare::generic.emergency_summary');
    $emergencyPhone = $section->emergencyPhone ?? $section->urgentPhone ?? null;
    $primaryAction = $section->primaryAction ?? $section->action ?? null;
    $phoneHref = $emergencyPhone ? preg_replace('/[^0-9+]/', '', (string) $emergencyPhone) : null;
@endphp

<section
    class="healthcare-emergency border-y border-[var(--healthcare-line)] bg-[var(--healthcare-ink)] text-white"
>
    <div
        class="grid gap-6 px-6 py-8 lg:grid-cols-[0.74fr_1.26fr] lg:items-center"
    >
        <div>
            <p
                class="text-xs font-black tracking-widest text-[var(--healthcare-accent)] uppercase"
            >
                {{ __('capell-theme-healthcare::generic.emergency_eyebrow') }}
            </p>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-white">
                {{ $heading }}
            </h2>
            @if ($summary)
                <p class="mt-3 max-w-xl text-sm leading-6 text-stone-200">
                    {{ $summary }}
                </p>
            @endif

            <div class="mt-5 flex flex-wrap gap-3">
                @if ($phoneHref)
                    <a
                        href="tel:{{ $phoneHref }}"
                        class="rounded-full bg-white px-5 py-3 text-sm font-black text-[var(--healthcare-ink)]"
                    >
                        {{ __('capell-theme-healthcare::generic.emergency_call_label', ['phone' => $emergencyPhone]) }}
                    </a>
                @endif

                @if (($primaryAction['url'] ?? null) && ($primaryAction['label'] ?? null))
                    <a
                        href="{{ $primaryAction['url'] }}"
                        class="rounded-full border border-white/30 px-5 py-3 text-sm font-black text-white"
                    >
                        {{ $primaryAction['label'] }}
                    </a>
                @elseif ($section->urgentCareUrl ?? null)
                    <a
                        href="{{ $section->urgentCareUrl }}"
                        class="rounded-full border border-white/30 px-5 py-3 text-sm font-black text-white"
                    >
                        {{ __('capell-theme-healthcare::generic.emergency_urgent_care_label') }}
                    </a>
                @endif
            </div>
        </div>

        <div class="grid gap-3 md:grid-cols-3">
            @forelse ($items as $item)
                <article
                    class="rounded-2xl border border-white/15 bg-white/10 p-4"
                >
                    <p
                        class="font-mono text-sm font-black text-[var(--healthcare-accent)]"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </p>
                    <h3 class="mt-4 text-base font-black text-white">
                        {{ $item['title'] ?? $item['label'] ?? '' }}
                    </h3>
                    @if (! empty($item['summary']))
                        <p class="mt-2 text-sm leading-6 text-stone-200">
                            {{ $item['summary'] }}
                        </p>
                    @endif
                </article>
            @empty
                @foreach ([
                              __('capell-theme-healthcare::generic.emergency_default_step_assess'),
                              __('capell-theme-healthcare::generic.emergency_default_step_call'),
                              __('capell-theme-healthcare::generic.emergency_default_step_route'),
                          ] as $fallbackStep)
                    <article
                        class="rounded-2xl border border-white/15 bg-white/10 p-4"
                    >
                        <p
                            class="font-mono text-sm font-black text-[var(--healthcare-accent)]"
                        >
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </p>
                        <h3 class="mt-4 text-base font-black text-white">
                            {{ $fallbackStep }}
                        </h3>
                    </article>
                @endforeach
            @endforelse
        </div>
    </div>
</section>
