@php
    $locations = $section->locations ?? $section->items ?? [];
@endphp

<section class="healthcare-contact bg-[var(--healthcare-surface)]">
    <div class="grid min-w-0 gap-8 px-6 lg:grid-cols-[0.9fr_1.1fr]">
        <div class="min-w-0">
            <h2
                class="text-4xl font-black tracking-tight text-[var(--healthcare-ink)]"
            >
                {{ $section->heading }}
            </h2>
            @if ($section->summary ?? null)
                <p class="mt-4 max-w-xl text-lg text-stone-600">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div
            class="theme-carousel relative mt-2 max-w-full min-w-0 overflow-hidden"
            data-carousel="healthcare-contact"
        >
            <div
                class="flex max-w-full snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 sm:grid sm:grid-cols-2 sm:overflow-visible sm:pr-0 [&::-webkit-scrollbar]:hidden"
                data-carousel-track
            >
                @forelse ($locations as $location)
                    <article
                        class="min-w-[260px] snap-start rounded-lg border border-[var(--healthcare-line)] bg-white p-6 sm:min-w-0"
                    >
                        <p
                            class="text-xs font-black tracking-widest text-[var(--healthcare-link)] uppercase"
                        >
                            {{ $location['label'] ?? __('capell-theme-healthcare::generic.location') }}
                        </p>
                        <h3 class="mt-3 text-xl font-black">
                            {{ $location['title'] ?? '' }}
                        </h3>
                        <p class="mt-2 text-sm">
                            {{ $location['summary'] ?? $location['address'] ?? '' }}
                        </p>
                        @if ($location['address'] ?? null)
                            <p
                                class="mt-4 text-xs font-black text-[var(--healthcare-ink)] uppercase"
                            >
                                {{ __('capell-theme-healthcare::generic.location_address') }}
                            </p>
                            <p class="mt-1 text-sm">
                                {{ $location['address'] }}
                            </p>
                        @endif

                        @if ($location['hours'] ?? $location['openingHours'] ?? null)
                            <p
                                class="mt-4 text-xs font-black text-[var(--healthcare-ink)] uppercase"
                            >
                                {{ __('capell-theme-healthcare::generic.location_hours') }}
                            </p>
                            <p class="mt-1 text-sm">
                                {{ $location['hours'] ?? $location['openingHours'] }}
                            </p>
                        @endif

                        @if ($location['phone'] ?? null)
                            <a
                                href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $location['phone']) }}"
                                class="mt-4 inline-flex text-sm font-black text-[var(--healthcare-primary)]"
                            >
                                {{ $location['phone'] }}
                            </a>
                        @endif

                        @if ($location['mapUrl'] ?? null)
                            <a
                                href="{{ $location['mapUrl'] }}"
                                class="mt-3 inline-flex text-sm font-black text-[var(--healthcare-link)]"
                            >
                                {{ __('capell-theme-healthcare::generic.location_map') }}
                            </a>
                        @endif
                    </article>
                @empty
                    <article
                        class="min-w-[260px] snap-start rounded-lg border border-dashed border-[var(--healthcare-line)] bg-white p-6 sm:min-w-0"
                    >
                        <p
                            class="text-xs font-black tracking-widest text-[var(--healthcare-link)] uppercase"
                        >
                            {{ __('capell-theme-healthcare::generic.location') }}
                        </p>
                        <h3
                            class="mt-3 text-xl font-black text-[var(--healthcare-ink)]"
                        >
                            {{ __('capell-theme-healthcare::generic.locations_ready') }}
                        </h3>
                    </article>
                @endforelse
            </div>

            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-[var(--healthcare-line)] bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="{{ __('capell-theme-healthcare::generic.carousel_previous') }}"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-[var(--healthcare-line)] bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="{{ __('capell-theme-healthcare::generic.carousel_next') }}"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>
</section>
