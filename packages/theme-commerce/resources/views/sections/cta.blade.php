@php
    $metrics = $section->stats ?? $section->metrics ?? [];
    $actions = $section->actions ?? [];
@endphp

<section class="retail-final-cta bg-stone-950/95">
    <div class="px-6 py-16">
        <div class="mx-auto max-w-6xl">
            <div
                class="grid gap-6 rounded-3xl border border-[var(--retail-primary)]/25 bg-[var(--retail-primary)] p-8 text-white shadow-[var(--retail-primary)]/20 shadow-2xl md:p-12"
            >
                <div class="grid gap-8 md:grid-cols-[1fr_auto] md:items-end">
                    <div>
                        <h2
                            class="text-3xl leading-tight font-black text-white sm:text-4xl md:text-5xl"
                        >
                            {{ $section->heading }}
                        </h2>
                        @if ($section->summary ?? null)
                            <p class="mt-4 max-w-2xl text-stone-100">
                                {{ $section->summary }}
                            </p>
                        @endif

                        @if (count($metrics) > 0)
                            <div
                                class="mt-8 grid max-w-2xl gap-3 sm:grid-cols-3"
                            >
                                @foreach ($metrics as $metric)
                                    <div class="rounded-xl bg-white/10 p-3">
                                        <p
                                            class="text-2xl font-black text-[var(--retail-warm)]"
                                        >
                                            {{ $metric['value'] ?? '' }}
                                        </p>
                                        <p
                                            class="text-xs font-bold tracking-wide text-white/80 uppercase"
                                        >
                                            {{ $metric['label'] ?? '' }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-3 md:justify-end">
                        @foreach ($actions as $action)
                            <a
                                href="{{ $action['url'] }}"
                                class="retail-cta {{ ($action['style'] ?? 'primary') === 'secondary' ? 'border-white/45 bg-white/5 text-white hover:bg-white/15' : 'bg-white text-[var(--retail-primary)] hover:bg-stone-100' }} rounded-full border px-6 py-3 text-sm font-black transition"
                            >
                                {{ $action['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div
                    class="relative mt-2 h-2 overflow-hidden rounded-full bg-[var(--retail-deep)]"
                >
                    <div
                        class="theme-cta-progress absolute inset-y-0 left-0 hidden w-1/3 animate-pulse rounded-full bg-white/90"
                        data-cta-progress
                    ></div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    ;(() => {
        const progress = document.querySelector('[data-cta-progress]')
        if (!progress) {
            return
        }

        let position = 0
        progress.classList.remove('hidden')

        setInterval(() => {
            position = (position + 1) % 3
            const map = ['0%', '33%', '66%']
            progress.style.left = map[position]
        }, 2400)
    })()
</script>
