@php
    $filters = $section->items ?? $section->filters ?? [];
@endphp

<section class="healthcare-finder bg-white">
    <div class="grid gap-8 px-6 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-[#14323a]">
                {{ $section->heading }}
            </h2>
            @if ($section->summary ?? null)
                <p class="mt-4 max-w-xl text-lg text-stone-600">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="healthcare-frame bg-[#f6fbfd] p-5">
            <div class="grid gap-3">
                @forelse ($filters as $filter)
                    <div
                        class="rounded-xl border border-stone-200 bg-white p-4"
                    >
                        <p class="text-xs font-black text-[#0f766e] uppercase">
                            {{ $filter['group'] ?? __('capell-theme-healthcare::generic.finder_filter') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (($filter['options'] ?? [$filter['title'] ?? $filter['label'] ?? '']) as $option)
                                <span
                                    class="rounded-full border border-stone-200 px-3 py-1 text-sm font-bold text-[#14323a]"
                                >
                                    {{ $option }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div
                        class="rounded-xl border border-dashed border-stone-200 bg-white p-4"
                    >
                        <p class="text-xs font-black text-[#0f766e] uppercase">
                            {{ __('capell-theme-healthcare::generic.finder_filter') }}
                        </p>
                        <h3 class="mt-3 text-lg font-black text-[#14323a]">
                            {{ __('capell-theme-healthcare::generic.finder_ready') }}
                        </h3>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
