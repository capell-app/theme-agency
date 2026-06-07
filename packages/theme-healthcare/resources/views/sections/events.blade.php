@php
    $eventsAvailable ??= false;
    $items = $section->items ?? [];
@endphp

<section class="healthcare-events bg-white">
    <div class="grid gap-8 px-6 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
        <div>
            <p
                class="mb-4 text-xs font-black tracking-widest text-[var(--healthcare-link)] uppercase"
            >
                {{ $eventsAvailable ? __('capell-theme-healthcare::generic.events_live') : __('capell-theme-healthcare::generic.events_static') }}
            </p>
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

        <div class="grid gap-3">
            <div class="relative">
                <div
                    class="theme-carousel"
                    data-carousel="healthcare-events"
                >
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="rounded-full border border-[var(--healthcare-line)] px-3 py-1 text-xs font-bold text-[var(--healthcare-primary)] transition hover:bg-[var(--healthcare-surface)]"
                            aria-label="{{ __('capell-theme-healthcare::generic.carousel_previous') }}"
                            data-carousel-prev
                        >
                            {{ __('capell-theme-healthcare::generic.carousel_previous') }}
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-[var(--healthcare-line)] px-3 py-1 text-xs font-bold text-[var(--healthcare-primary)] transition hover:bg-[var(--healthcare-surface)]"
                            aria-label="{{ __('capell-theme-healthcare::generic.carousel_next') }}"
                            data-carousel-next
                        >
                            {{ __('capell-theme-healthcare::generic.carousel_next') }}
                        </button>
                    </div>

                    <div
                        class="mt-3 grid gap-3 lg:grid-cols-1 xl:-mx-2 xl:flex xl:snap-x xl:snap-mandatory xl:[scrollbar-width:none] xl:overflow-x-auto xl:scroll-smooth xl:pb-4"
                        role="list"
                        aria-live="polite"
                        data-carousel-track
                    >
                        @forelse ($items as $item)
                            @if ($eventsAvailable)
                                <a
                                    href="{{ $item['url'] ?? '#' }}"
                                    class="grid gap-4 rounded-lg border border-[var(--healthcare-line)] bg-[var(--healthcare-surface)] p-5 transition hover:-translate-y-1 hover:border-[var(--healthcare-link)] hover:shadow-sm md:grid-cols-[8rem_1fr] xl:min-w-[22rem] xl:flex-shrink-0 xl:snap-start xl:rounded-xl xl:px-6"
                                >
                                    <p
                                        class="text-sm font-black text-[var(--healthcare-primary)]"
                                    >
                                        {{ $item['date'] ?? __('capell-theme-healthcare::generic.next_available') }}
                                    </p>
                                    <div>
                                        <h3 class="text-xl font-black">
                                            {{ $item['title'] ?? '' }}
                                        </h3>
                                        <p class="mt-2 text-sm">
                                            {{ $item['summary'] ?? '' }}
                                        </p>
                                    </div>
                                </a>
                            @else
                                <article
                                    class="grid gap-4 rounded-lg border border-[var(--healthcare-line)] bg-[var(--healthcare-surface)] p-5 md:grid-cols-[8rem_1fr]"
                                >
                                    <p
                                        class="text-sm font-black text-[var(--healthcare-primary)]"
                                    >
                                        {{ $item['date'] ?? __('capell-theme-healthcare::generic.next_available') }}
                                    </p>
                                    <div>
                                        <h3 class="text-xl font-black">
                                            {{ $item['title'] ?? '' }}
                                        </h3>
                                        <p class="mt-2 text-sm">
                                            {{ $item['summary'] ?? '' }}
                                        </p>
                                    </div>
                                </article>
                            @endif
                        @empty
                            <article
                                class="grid gap-4 rounded-lg border border-dashed border-[var(--healthcare-line)] bg-[var(--healthcare-surface)] p-5 md:grid-cols-[8rem_1fr]"
                            >
                                <p
                                    class="text-sm font-black text-[var(--healthcare-primary)]"
                                >
                                    {{ __('capell-theme-healthcare::generic.next_available') }}
                                </p>
                                <div>
                                    <h3 class="text-xl font-black">
                                        {{ __('capell-theme-healthcare::generic.events_static') }}
                                    </h3>
                                    <p class="mt-2 text-sm">
                                        {{ __('capell-theme-healthcare::generic.care_pathway_ready') }}
                                    </p>
                                </div>
                            </article>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
