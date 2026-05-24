<section class="velocity-directory bg-white">
    <div class="px-6">
        <div class="grid gap-4 md:grid-cols-[0.75fr_1fr] md:items-end">
            <h2>{{ $section->heading }}</h2>
            @if ($section->summary)
                <p class="max-w-2xl text-lg md:justify-self-end">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ($section->items as $item)
                <a
                    href="{{ $item['url'] ?? '#' }}"
                    class="group rounded-2xl border border-slate-200 bg-slate-50/70 p-6 transition hover:border-blue-300 hover:bg-white"
                >
                    @if ($item['type'] ?? null)
                        <p
                            class="mb-4 text-xs font-black uppercase tracking-widest text-cyan-700"
                        >
                            {{ $item['type'] }}
                        </p>
                    @endif

                    <h3 class="text-xl font-black group-hover:text-blue-700">
                        {{ $item['title'] }}
                    </h3>
                    <p class="mt-3 text-sm">
                        {{ $item['summary'] ?? '' }}
                    </p>
                </a>
            @endforeach
        </div>
    </div>
</section>
