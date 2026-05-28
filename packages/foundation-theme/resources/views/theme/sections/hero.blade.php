<section
    class="theme-hero border-b border-slate-200/80 bg-[var(--theme-surface)]"
>
    <div
        class="mx-auto grid max-w-7xl items-center gap-8 px-4 py-12 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:py-20"
    >
        <div class="space-y-5">
            @if ($section->eyebrow)
                <p
                    class="text-xs font-semibold tracking-[0.16em] text-[var(--theme-primary)] uppercase"
                >
                    {{ $section->eyebrow }}
                </p>
            @endif

            <h1
                class="max-w-4xl text-4xl leading-tight font-[var(--theme-heading-font)] font-semibold text-slate-950 sm:text-5xl lg:text-6xl"
            >
                {{ $section->heading }}
            </h1>

            @if ($section->summary)
                <p
                    class="max-w-2xl text-base leading-8 text-slate-600 sm:text-lg"
                >
                    {{ $section->summary }}
                </p>
            @endif

            @if ($section->actions !== [])
                <div class="flex flex-wrap gap-3">
                    @foreach ($section->actions as $action)
                        <a
                            href="{{ $action['url'] }}"
                            class="{{ ($action['style'] ?? 'primary') === 'secondary' ? 'border border-slate-300 text-slate-800 hover:border-slate-950' : 'bg-[var(--theme-primary)] text-white hover:opacity-90' }} rounded-full px-5 py-3 text-sm font-semibold transition"
                        >
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($section->mediaUrl)
            <figure
                class="overflow-hidden rounded-[var(--theme-radius-value)] border border-slate-200 bg-white shadow-sm"
            >
                <img
                    src="{{ $section->mediaUrl }}"
                    alt="{{ $section->mediaAlt ?? '' }}"
                    class="aspect-[16/10] w-full object-cover"
                />
            </figure>
        @endif
    </div>
</section>
