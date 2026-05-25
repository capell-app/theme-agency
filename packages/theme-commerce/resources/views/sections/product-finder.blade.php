@php
    $filters = $section->items ?? $section->filters ?? [];
@endphp

<section class="retail-finder bg-white">
    <div class="grid gap-8 px-6 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
        <div>
            <h2>{{ $section->heading }}</h2>
            @if ($section->summary ?? null)
                <p class="mt-4 max-w-xl text-lg">{{ $section->summary }}</p>
            @endif
        </div>

        <div class="retail-frame bg-[#fffaf3] p-5">
            <div class="grid gap-3">
                @foreach ($filters as $filter)
                    <div
                        class="rounded-xl border border-stone-200 bg-white p-4"
                    >
                        <p class="text-xs font-black text-[#1f5f4a] uppercase">
                            {{ $filter['group'] ?? __('capell-theme-commerce::generic.finder_filter') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (($filter['options'] ?? [$filter['title'] ?? $filter['label'] ?? '']) as $option)
                                <span
                                    class="rounded-full border border-stone-200 px-3 py-1 text-sm font-bold text-[#17211c]"
                                >
                                    {{ $option }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
