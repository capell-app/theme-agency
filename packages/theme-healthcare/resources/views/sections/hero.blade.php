<section class="healthcare-hero relative overflow-hidden">
    <div
        class="pointer-events-none absolute inset-x-0 top-0 h-40 border-b border-stone-200 bg-[var(--healthcare-surface)]"
    ></div>

    <div
        class="relative grid items-center gap-12 px-6 lg:grid-cols-[1.02fr_0.98fr]"
    >
        <div>
            @if ($section->eyebrow ?? null)
                <p
                    class="mb-5 inline-flex rounded-full border border-[var(--healthcare-accent)]/25 bg-white px-3 py-1 text-xs font-black tracking-widest text-[var(--healthcare-primary)] uppercase"
                >
                    {{ $section->eyebrow }}
                </p>
            @endif

            <h1
                class="max-w-2xl text-4xl leading-none font-black tracking-tight text-[var(--healthcare-ink)] lg:text-5xl"
            >
                {{ $section->heading }}
            </h1>

            @if ($section->summary ?? null)
                <p class="mt-7 max-w-2xl text-xl">
                    {{ $section->summary }}
                </p>
            @endif

            <div class="mt-9 flex flex-wrap gap-3">
                @foreach (($section->actions ?? []) as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="healthcare-cta {{ ($action['style'] ?? 'primary') === 'secondary' ? 'healthcare-cta-secondary' : 'healthcare-cta-primary' }}"
                    >
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="healthcare-frame bg-white p-3">
            @if ($section->mediaUrl ?? null)
                <img
                    src="{{ $section->mediaUrl }}"
                    alt="{{ $section->mediaAlt ?? '' }}"
                    width="1200"
                    height="900"
                    fetchpriority="high"
                    decoding="async"
                    class="aspect-[4/3] w-full rounded-xl object-cover"
                />
            @else
                <div
                    class="aspect-[4/3] rounded-xl bg-[var(--healthcare-ink)] p-5 text-white"
                >
                    <div
                        class="grid h-full grid-rows-[auto_1fr_auto] gap-5 rounded-lg border border-white/10 bg-white/[0.03] p-5"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="h-2.5 w-24 rounded-full bg-[var(--healthcare-accent)]"
                            ></span>
                            <span
                                class="h-2.5 w-14 rounded-full bg-[var(--healthcare-primary)]"
                            ></span>
                        </div>
                        <div class="grid content-end gap-3">
                            <span
                                class="h-24 rounded-lg border border-white/10 bg-white/10"
                            ></span>
                            <span
                                class="h-3 w-2/3 rounded-full bg-white/40"
                            ></span>
                            <span
                                class="h-3 w-1/2 rounded-full bg-white/25"
                            ></span>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <span
                                class="h-16 rounded-lg bg-[var(--healthcare-accent)]/25"
                            ></span>
                            <span
                                class="h-16 rounded-lg bg-[var(--healthcare-primary)]/25"
                            ></span>
                            <span class="h-16 rounded-lg bg-white/10"></span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
