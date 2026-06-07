<section class="theme-proof mx-auto max-w-7xl px-6 py-20">
    <div class="rounded-[2rem] bg-white p-8 text-zinc-950 shadow-sm">
        <div class="grid gap-5 lg:grid-cols-[0.68fr_1.32fr] lg:items-end">
            <div>
                <p
                    class="text-sm font-black tracking-[0.24em] text-[var(--theme-primary)] uppercase"
                >
                    {{ __('capell-theme-agency::generic.proof_wall') }}
                </p>
                <h2 class="mt-4 text-4xl font-black tracking-tight">
                    {{ $section->heading }}
                </h2>
            </div>
            @if ($section->summary)
                <p class="max-w-2xl text-zinc-600 lg:justify-self-end">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div
            class="theme-carousel relative mt-8"
            data-carousel="proof"
        >
            <p
                class="sr-only"
                aria-live="polite"
                data-carousel-status
                data-carousel-scrollable-label="{{ __('capell-theme-agency::generic.carousel_scrollable') }}"
                data-carousel-static-label="{{ __('capell-theme-agency::generic.carousel_static') }}"
            >
                {{ __('capell-theme-agency::generic.carousel_static') }}
            </p>

            <div
                class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 [&::-webkit-scrollbar]:hidden"
                data-carousel-track
            >
                @foreach ($section->items as $item)
                    <figure
                        class="min-w-[250px] snap-start rounded-2xl bg-zinc-950 p-5 text-white md:min-w-0"
                    >
                        @if (! empty($item['image']))
                            <img
                                src="{{ $item['image'] }}"
                                alt="{{ $item['name'] ?? $item['logo'] ?? '' }}"
                                class="mb-4 aspect-[16/10] w-full rounded-lg object-cover opacity-90"
                            />
                        @endif

                        <p class="font-mono text-4xl font-black">
                            {{ $item['metric'] ?? str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </p>
                        <blockquote
                            class="mt-4 text-sm leading-6 text-zinc-200"
                        >
                            {{ $item['quote'] ?? $item['summary'] ?? '' }}
                        </blockquote>
                        <figcaption
                            class="mt-5 border-t border-white/10 pt-4 text-sm font-black text-[var(--theme-accent)]"
                        >
                            {{ $item['name'] ?? $item['logo'] ?? '' }}
                        </figcaption>
                    </figure>
                @endforeach
            </div>

            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-white/20 bg-white/10 p-2 text-sm font-semibold text-white shadow-md"
                aria-label="{{ __('capell-theme-agency::generic.carousel_previous_proof') }}"
                aria-disabled="true"
                data-carousel-prev
            >
                <span aria-hidden="true">←</span>
                <span class="sr-only">
                    {{ __('capell-theme-agency::generic.carousel_previous_proof') }}
                </span>
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-white/20 bg-white/10 p-2 text-sm font-semibold text-white shadow-md"
                aria-label="{{ __('capell-theme-agency::generic.carousel_next_proof') }}"
                aria-disabled="true"
                data-carousel-next
            >
                <span aria-hidden="true">→</span>
                <span class="sr-only">
                    {{ __('capell-theme-agency::generic.carousel_next_proof') }}
                </span>
            </button>
        </div>
    </div>
</section>
