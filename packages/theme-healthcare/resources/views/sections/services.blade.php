@php
    $services = $section->features ?? $section->items ?? [];
    $serviceCount = is_countable($services) ? count($services) : 0;
    $usesCarousel = $serviceCount > 4;
    $gridClass = match ($serviceCount) {
        1 => 'grid gap-4',
        2 => 'grid gap-4 md:grid-cols-2',
        3 => 'grid gap-4 md:grid-cols-3',
        default => 'grid gap-4 sm:grid-cols-2 lg:grid-cols-4',
    };
@endphp

<section class="healthcare-services bg-white">
    <div class="px-6">
        <div
            class="flex flex-col justify-between gap-5 md:flex-row md:items-end"
        >
            <div>
                <h2
                    class="text-4xl font-black tracking-tight text-[var(--healthcare-ink)]"
                >
                    {{ $section->heading }}
                </h2>
                @if ($section->summary ?? null)
                    <p class="mt-4 max-w-2xl text-lg text-stone-600">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>
        </div>

        <div
            class="theme-carousel relative mt-10 max-w-full min-w-0 overflow-hidden"
            data-carousel="healthcare-services"
        >
            <div
                class="{{ $usesCarousel ? 'flex max-w-full snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 md:overflow-visible md:pr-0 lg:grid-cols-4 [&::-webkit-scrollbar]:hidden' : $gridClass }}"
                data-carousel-track
            >
                @forelse ($services as $service)
                    <article
                        class="{{ $usesCarousel ? 'min-w-[250px] snap-start sm:min-w-[270px] md:min-w-0' : '' }} rounded-xl border border-stone-200 bg-[var(--healthcare-surface)] p-3 transition hover:-translate-y-1 hover:border-[var(--healthcare-primary)] hover:shadow-lg"
                    >
                        @if ($service['image'] ?? $service['imageUrl'] ?? null)
                            <img
                                src="{{ $service['image'] ?? $service['imageUrl'] }}"
                                alt="{{ $service['imageAlt'] ?? '' }}"
                                width="800"
                                height="800"
                                loading="lazy"
                                decoding="async"
                                class="aspect-square w-full rounded-lg object-cover"
                            />
                        @else
                            <div
                                class="flex h-32 items-end rounded-lg border border-[var(--healthcare-line)] bg-[var(--healthcare-ink)] p-4 text-white"
                            >
                                <div>
                                    <p
                                        class="text-[0.65rem] font-black tracking-widest text-[var(--healthcare-primary-bright)] uppercase"
                                    >
                                        {{ $service['icon'] ?? $service['type'] ?? __('capell-theme-healthcare::generic.service_label') }}
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-black text-white"
                                    >
                                        {{ $service['metric'] ?? __('capell-theme-healthcare::generic.care_pathway') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                        <div class="p-2">
                            <h3 class="text-lg font-black">
                                {{ $service['title'] }}
                            </h3>
                            <p class="mt-2 text-sm">
                                {{ $service['description'] ?? $service['summary'] ?? '' }}
                            </p>
                            @if ($service['price'] ?? $service['metric'] ?? null)
                                <p
                                    class="mt-4 text-sm font-black text-[var(--healthcare-primary)]"
                                >
                                    {{ $service['price'] ?? $service['metric'] }}
                                </p>
                            @endif
                        </div>
                    </article>
                @empty
                    <article
                        class="rounded-xl border border-dashed border-stone-200 bg-[var(--healthcare-surface)] p-6"
                    >
                        <p
                            class="text-xs font-black text-[var(--healthcare-primary)] uppercase"
                        >
                            {{ __('capell-theme-healthcare::generic.service_label') }}
                        </p>
                        <h3
                            class="mt-3 text-lg font-black text-[var(--healthcare-ink)]"
                        >
                            {{ __('capell-theme-healthcare::generic.services_ready') }}
                        </h3>
                    </article>
                @endforelse
            </div>

            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="{{ __('capell-theme-healthcare::generic.carousel_previous') }}"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="{{ __('capell-theme-healthcare::generic.carousel_next') }}"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>
</section>
