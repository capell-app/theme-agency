<section class="saas-hero relative overflow-hidden">
    <div
        class="pointer-events-none absolute inset-x-0 top-0 h-40 border-b border-cyan-100 bg-cyan-50/45"
    ></div>

    <div
        class="relative grid items-center gap-12 px-6 lg:grid-cols-[1.02fr_0.98fr]"
    >
        <div>
            @if ($section->eyebrow)
                <p
                    class="mb-5 inline-flex rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-black tracking-widest text-cyan-700 uppercase"
                >
                    {{ $section->eyebrow }}
                </p>
            @endif

            <h1
                class="max-w-2xl text-4xl font-black tracking-tight text-slate-950 lg:text-5xl"
            >
                {{ $section->heading }}
            </h1>

            @if ($section->summary)
                <p class="mt-7 max-w-2xl text-xl">
                    {{ $section->summary }}
                </p>
            @endif

            <div class="mt-9 flex flex-wrap gap-3">
                @foreach ($section->actions as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="saas-cta {{ ($action['style'] ?? 'primary') === 'secondary' ? 'saas-cta-secondary' : 'saas-cta-primary' }}"
                    >
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="saas-frame bg-white p-3">
            @if ($section->mediaUrl)
                <img
                    src="{{ $section->mediaUrl }}"
                    alt="{{ $section->mediaAlt ?? '' }}"
                    class="aspect-[4/3] w-full rounded-xl object-cover"
                />
            @else
                <div
                    class="aspect-[4/3] rounded-xl bg-slate-950 p-5 text-white"
                >
                    <div
                        class="grid h-full grid-rows-[auto_1fr_auto] gap-5 rounded-lg border border-white/10 bg-white/[0.03] p-5"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="h-2.5 w-24 rounded-full bg-cyan-300"
                            ></span>
                            <span
                                class="h-2.5 w-14 rounded-full bg-blue-400"
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
                            <span class="h-16 rounded-lg bg-cyan-400/25"></span>
                            <span class="h-16 rounded-lg bg-blue-400/25"></span>
                            <span class="h-16 rounded-lg bg-white/10"></span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
