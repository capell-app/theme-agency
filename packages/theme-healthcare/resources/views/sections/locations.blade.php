@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-healthcare::generic.locations_label');
    $summary ??= $section->summary ?? null;
@endphp

<section class="theme-section theme-section-locations bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-6 md:grid-cols-[0.75fr_1fr]">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#0f766e] uppercase"
                >
                    {{ __('capell-theme-healthcare::generic.locations_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black text-[#14323a]">
                    {{ $heading }}
                </h2>
                @if ($summary)
                    <p class="mt-4 text-base leading-8 text-slate-600">
                        {{ $summary }}
                    </p>
                @endif
            </div>

            <div class="grid gap-4">
                @forelse ($items as $item)
                    <article
                        class="grid gap-2 border border-[#d9e8ee] bg-[#f6fbfd] p-5"
                    >
                        <p class="text-xs font-black text-[#0f766e] uppercase">
                            {{ $item['type'] ?? __('capell-theme-healthcare::generic.location') }}
                        </p>
                        <h3 class="text-xl font-black text-[#14323a]">
                            {{ $item['title'] ?? __('capell-theme-healthcare::generic.location') }}
                        </h3>
                        <p class="text-sm leading-6 text-slate-600">
                            {{ $item['summary'] ?? __('capell-theme-healthcare::generic.locations_ready') }}
                        </p>
                    </article>
                @empty
                    <article
                        class="border border-dashed border-[#d9e8ee] bg-[#f6fbfd] p-6"
                    >
                        <h3 class="text-lg font-black text-[#14323a]">
                            {{ __('capell-theme-healthcare::generic.locations_ready') }}
                        </h3>
                    </article>
                @endforelse
            </div>
        </div>
    </div>
</section>
