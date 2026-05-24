@php
    $items = $section->items ?? $section->features ?? [];
@endphp

<section class="velocity-comparison bg-slate-50">
    <div class="px-6">
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="mx-auto">{{ $section->heading }}</h2>
            @if ($section->summary ?? null)
                <p class="mx-auto mt-4 max-w-2xl text-lg">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div
            class="mt-10 overflow-hidden rounded-2xl border border-slate-200 bg-white"
        >
            @foreach ($items as $item)
                <div
                    class="grid gap-4 border-b border-slate-200 p-5 last:border-b-0 md:grid-cols-[0.45fr_1fr] md:items-center"
                >
                    <h3 class="text-lg font-black">
                        {{ $item['title'] ?? $item['label'] ?? '' }}
                    </h3>
                    <p class="text-sm">
                        {{ $item['description'] ?? $item['summary'] ?? '' }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
