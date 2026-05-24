@php
    $items = $section->items ?? $section->features ?? [];
@endphp

<section class="velocity-calculator bg-white">
    <div class="grid gap-8 px-6 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
        <div>
            <h2>{{ $section->heading }}</h2>
            @if ($section->summary ?? null)
                <p class="mt-4 max-w-xl text-lg">{{ $section->summary }}</p>
            @endif
        </div>

        <div class="velocity-frame bg-slate-950 p-6 text-white">
            <div class="grid gap-4">
                @foreach ($items as $item)
                    <div
                        class="rounded-xl border border-white/10 bg-white/[0.05] p-4"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="font-black text-white">
                                {{ $item['title'] ?? $item['label'] ?? '' }}
                            </h3>
                            @if ($item['metric'] ?? null)
                                <span
                                    class="rounded-full bg-cyan-300 px-3 py-1 text-xs font-black text-slate-950"
                                >
                                    {{ $item['metric'] }}
                                </span>
                            @endif
                        </div>
                        @if ($item['description'] ?? $item['summary'] ?? null)
                            <p class="mt-2 text-sm text-slate-300">
                                {{ $item['description'] ?? $item['summary'] }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
