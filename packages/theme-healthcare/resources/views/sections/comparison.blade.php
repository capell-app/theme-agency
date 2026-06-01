@php
    $items = $section->items ?? $section->features ?? [];
@endphp

<section class="healthcare-comparison bg-[#f6fbfd]">
    <div class="px-6">
        <div class="mx-auto max-w-3xl text-center">
            <h2
                class="mx-auto text-4xl font-black tracking-tight text-[#14323a]"
            >
                {{ $section->heading }}
            </h2>
            @if ($section->summary ?? null)
                <p class="mx-auto mt-4 max-w-2xl text-lg text-stone-600">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div
            class="theme-carousel relative mt-10"
            data-carousel="healthcare-comparison"
        >
            <div
                class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-2 md:overflow-visible md:pr-0 [&::-webkit-scrollbar]:hidden"
                data-carousel-track
            >
                @foreach ($items as $item)
                    <article
                        class="min-w-[300px] snap-start rounded-xl border border-stone-200 bg-white p-5 md:min-w-0"
                    >
                        <h3 class="text-lg font-black text-[#14323a]">
                            {{ $item['title'] ?? $item['label'] ?? '' }}
                        </h3>
                        <p class="mt-3 text-sm">
                            {{ $item['description'] ?? $item['summary'] ?? '' }}
                        </p>
                    </article>
                @endforeach
            </div>

            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold text-[#0f766e] shadow-md"
                aria-label="{{ __('capell-theme-healthcare::generic.carousel_previous') }}"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold text-[#0f766e] shadow-md"
                aria-label="{{ __('capell-theme-healthcare::generic.carousel_next') }}"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>
</section>
