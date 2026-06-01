@php
    $services = $section->items ?? [
        ['category' => 'Core', 'title' => 'Emergency Repair'],
        ['category' => 'Growth', 'title' => 'Preventive Care'],
        ['category' => 'Delivery', 'title' => 'Maintenance'],
        ['category' => 'Support', 'title' => 'Support Plans'],
    ];
@endphp

<section class="theme-section theme-section-services">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <p class="text-xs font-black tracking-[0.2em] text-slate-500">
                Services
            </p>
            <h2 class="mt-3 text-4xl font-black tracking-tight text-[#17211c]">
                {{ $heading }}
            </h2>
        @endisset

        <p class="mt-4 max-w-2xl text-stone-600">
            {{ __('capell-theme-local-services::generic.services_copy') }}
        </p>
        <div
            class="theme-carousel relative mt-8"
            data-carousel="local-services-services"
        >
            <div
                class="flex gap-4 overflow-x-auto pr-6 pb-2 sm:grid sm:grid-cols-2 sm:overflow-x-visible sm:pr-0 sm:pb-0 lg:grid-cols-4"
                data-carousel-track
            >
                @foreach ($services as $service)
                    <article
                        class="min-w-[220px] snap-start rounded-xl border border-stone-200 bg-white p-4 transition hover:-translate-y-1 hover:border-[#17211c] hover:bg-stone-950 hover:text-white"
                    >
                        <p
                            class="text-xs font-black tracking-widest text-[#17211c] sm:text-stone-500 dark:text-white"
                        >
                            {{ $service['category'] ?? 'Service' }}
                        </p>
                        <h3 class="mt-2 text-lg font-black">
                            {{ $service['title'] }}
                        </h3>
                        <p
                            class="mt-2 text-sm text-stone-600 dark:text-stone-300"
                        >
                            {{ $service['summary'] ?? '' }}
                        </p>
                    </article>
                @endforeach
            </div>
            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="{{ __('capell-theme-local-services::generic.carousel_previous') }}"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="{{ __('capell-theme-local-services::generic.carousel_next') }}"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>
</section>
