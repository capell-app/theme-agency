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
            <h2>{{ $section->heading }}</h2>
            @if ($section->summary ?? null)
                <p class="mt-4 max-w-xl text-lg">{{ $section->summary }}</p>
            @endif
        </div>

        <div class="grid gap-3">
            @foreach ($items as $item)
                @if ($eventsAvailable)
                    <a
                        href="{{ $item['url'] ?? '#' }}"
                        class="grid gap-4 rounded-lg border border-[#d9e8ee] bg-[#f6fbfd] p-5 transition hover:border-[#2563eb] md:grid-cols-[8rem_1fr]"
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
</section>
