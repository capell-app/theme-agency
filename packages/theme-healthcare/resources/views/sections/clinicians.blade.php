<section class="healthcare-care paths bg-[var(--healthcare-surface)]">
    <div class="px-6">
        <div class="grid gap-4 md:grid-cols-[0.75fr_1fr] md:items-end">
            <h2
                class="text-4xl font-black tracking-tight text-[var(--healthcare-ink)]"
            >
                {{ $section->heading }}
            </h2>
            @if ($section->summary ?? null)
                <p class="max-w-2xl text-lg text-stone-600 md:justify-self-end">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div
            class="theme-carousel relative mt-10"
            data-carousel="healthcare-clinicians"
        >
            <div
                class="flex snap-x snap-mandatory [scrollbar-width:none] gap-5 overflow-x-auto pr-6 pb-2 lg:grid lg:grid-cols-4 lg:overflow-visible lg:pr-0 [&::-webkit-scrollbar]:hidden"
                data-carousel-track
            >
                @forelse (($section->items ?? []) as $item)
                    <a
                        href="{{ $item['url'] ?? '#' }}"
                        class="group min-w-[240px] snap-start overflow-hidden rounded-xl border border-stone-200 bg-white transition hover:-translate-y-1 hover:border-[var(--healthcare-primary)] hover:shadow-lg lg:min-w-0"
                    >
                        @if ($item['image'] ?? $item['imageUrl'] ?? null)
                            <img
                                src="{{ $item['image'] ?? $item['imageUrl'] }}"
                                alt="{{ $item['imageAlt'] ?? '' }}"
                                width="800"
                                height="600"
                                loading="lazy"
                                decoding="async"
                                class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-105"
                            />
                        @endif

                        <div class="p-5">
                            @if ($item['type'] ?? null)
                                <p
                                    class="mb-4 text-xs font-black tracking-widest text-[var(--healthcare-primary)] uppercase"
                                >
                                    {{ $item['type'] }}
                                </p>
                            @endif

                            <h3
                                class="text-xl font-black group-hover:text-[var(--healthcare-primary)]"
                            >
                                {{ $item['title'] }}
                            </h3>
                            <p class="mt-3 text-sm">
                                {{ $item['summary'] ?? '' }}
                            </p>
                        </div>
                    </a>
                @empty
                    <article
                        class="min-w-[240px] snap-start rounded-xl border border-dashed border-stone-200 bg-white p-6 lg:min-w-0"
                    >
                        <p
                            class="text-xs font-black tracking-widest text-[var(--healthcare-primary)] uppercase"
                        >
                            {{ __('capell-theme-healthcare::generic.clinical_trust_label') }}
                        </p>
                        <h3
                            class="mt-3 text-xl font-black text-[var(--healthcare-ink)]"
                        >
                            {{ __('capell-theme-healthcare::generic.team_ready') }}
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
