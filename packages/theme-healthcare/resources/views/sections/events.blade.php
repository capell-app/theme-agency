@php
    $eventsAvailable ??= false;
    $items = $section->items ?? [];
@endphp

<section class="healthcare-events bg-white">
    <div class="grid gap-8 px-6 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
        <div>
            <p
                class="mb-4 text-xs font-black tracking-widest text-[#2563eb] uppercase"
            >
                {{ $eventsAvailable ? __('capell-theme-healthcare::generic.events_live') : __('capell-theme-healthcare::generic.events_static') }}
            </p>
            <h2 class="text-4xl font-black tracking-tight text-[#14323a]">
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
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        id="healthcare-events-prev"
                        class="rounded-full border border-[#d9e8ee] px-3 py-1 text-xs font-bold text-[#0f766e] transition hover:bg-[#f6fbfd]"
                        aria-label="Previous events"
                    >
                        Previous
                    </button>
                    <button
                        type="button"
                        id="healthcare-events-next"
                        class="rounded-full border border-[#d9e8ee] px-3 py-1 text-xs font-bold text-[#0f766e] transition hover:bg-[#f6fbfd]"
                        aria-label="Next events"
                    >
                        Next
                    </button>
                </div>

                <div
                    id="healthcare-events-track"
                    class="mt-3 grid gap-3 lg:grid-cols-1 xl:-mx-2 xl:flex xl:snap-x xl:snap-mandatory xl:[scrollbar-width:none] xl:overflow-x-auto xl:scroll-smooth xl:pb-4"
                    role="list"
                    aria-live="polite"
                >
                    @foreach ($items as $item)
                        @if ($eventsAvailable)
                            <a
                                href="{{ $item['url'] ?? '#' }}"
                                class="grid gap-4 rounded-lg border border-[#d9e8ee] bg-[#f6fbfd] p-5 transition hover:-translate-y-1 hover:border-[#2563eb] hover:shadow-sm md:grid-cols-[8rem_1fr] xl:min-w-[22rem] xl:flex-shrink-0 xl:snap-start xl:rounded-xl xl:px-6"
                            >
                                <p class="text-sm font-black text-[#0f766e]">
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
                                class="grid gap-4 rounded-lg border border-[#d9e8ee] bg-[#f6fbfd] p-5 md:grid-cols-[8rem_1fr]"
                            >
                                <p class="text-sm font-black text-[#0f766e]">
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
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    ;(function () {
        const prevButton = document.getElementById('healthcare-events-prev')
        const nextButton = document.getElementById('healthcare-events-next')
        const track = document.getElementById('healthcare-events-track')

        if (
            !track ||
            !prevButton ||
            !nextButton ||
            track.dataset.healthcareScrollable
        ) {
            return
        }

        if (track.scrollWidth <= track.clientWidth) {
            prevButton.hidden = true
            nextButton.hidden = true
        }

        track.dataset.healthcareScrollable = 'true'

        const slideAmount = 280

        prevButton.addEventListener('click', () => {
            track.scrollBy({ left: -slideAmount, behavior: 'smooth' })
        })

        nextButton.addEventListener('click', () => {
            track.scrollBy({ left: slideAmount, behavior: 'smooth' })
        })
    })()
</script>
