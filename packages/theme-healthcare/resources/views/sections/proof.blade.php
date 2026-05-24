<section
    class="healthcare-proof border-y border-stone-200 bg-[#14323a] text-white"
>
    <div class="px-6">
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="mx-auto text-white">{{ $section->heading }}</h2>
            @if ($section->summary ?? null)
                <p class="mx-auto mt-4 max-w-2xl text-stone-300">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach (($section->items ?? []) as $item)
                <figure
                    class="rounded-2xl border border-white/10 bg-white/[0.04] p-6"
                >
                    <blockquote class="text-2xl font-black text-white">
                        {{ $item['metric'] ?? $item['quote'] ?? '' }}
                    </blockquote>
                    <figcaption class="mt-4 text-sm font-bold text-[#f59e0b]">
                        {{ $item['name'] ?? $item['logo'] ?? '' }}
                    </figcaption>
                    @if ($item['role'] ?? null)
                        <p class="mt-1 text-sm text-stone-400">
                            {{ $item['role'] }}
                        </p>
                    @endif
                </figure>
            @endforeach
        </div>
    </div>
</section>
