<section class="saas-proof border-y border-slate-200 bg-slate-950 text-white">
    <div class="px-6">
        <div class="mx-auto max-w-3xl text-center">
            <h2
                class="mx-auto max-w-3xl text-4xl font-black tracking-tight text-white"
            >
                {{ $section->heading }}
            </h2>
            @if ($section->summary)
                <p class="mx-auto mt-4 max-w-2xl text-slate-300">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ($section->items as $item)
                <figure
                    class="rounded-2xl border border-white/10 bg-white/[0.04] p-6"
                >
                    <blockquote class="text-2xl font-black text-white">
                        {{ $item['metric'] ?? $item['quote'] ?? '' }}
                    </blockquote>
                    <figcaption class="mt-4 text-sm font-bold text-cyan-200">
                        {{ $item['name'] ?? $item['logo'] ?? '' }}
                    </figcaption>
                    @if ($item['role'] ?? null)
                        <p class="mt-1 text-sm text-slate-400">
                            {{ $item['role'] }}
                        </p>
                    @endif
                </figure>
            @endforeach
        </div>
    </div>
</section>
