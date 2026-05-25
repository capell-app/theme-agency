<section class="healthcare-care paths bg-[#f6fbfd]">
    <div class="px-6">
        <div class="grid gap-4 md:grid-cols-[0.75fr_1fr] md:items-end">
            <h2>{{ $section->heading }}</h2>
            @if ($section->summary ?? null)
                <p class="max-w-2xl text-lg md:justify-self-end">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-5 md:grid-cols-3">
            @foreach (($section->items ?? []) as $item)
                <a
                    href="{{ $item['url'] ?? '#' }}"
                    class="group overflow-hidden rounded-xl border border-stone-200 bg-white transition hover:border-[#0f766e]"
                >
                    @if ($item['image'] ?? $item['imageUrl'] ?? null)
                        <img
                            src="{{ $item['image'] ?? $item['imageUrl'] }}"
                            alt="{{ $item['imageAlt'] ?? '' }}"
                            class="aspect-[4/3] w-full object-cover"
                        />
                    @endif

                    <div class="p-5">
                        @if ($item['type'] ?? null)
                            <p
                                class="mb-4 text-xs font-black tracking-widest text-[#0f766e] uppercase"
                            >
                                {{ $item['type'] }}
                            </p>
                        @endif

                        <h3
                            class="text-xl font-black group-hover:text-[#0f766e]"
                        >
                            {{ $item['title'] }}
                        </h3>
                        <p class="mt-3 text-sm">
                            {{ $item['summary'] ?? '' }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
