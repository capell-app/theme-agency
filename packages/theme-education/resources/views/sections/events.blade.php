@php
    use Capell\Core\Facades\CapellCore;

    $eventsAvailable ??= CapellCore::isPackageInstalled('capell-app/events');
    $fallbackEventCards = __('capell-theme-education::generic.event_cards');
    $fallbackEventCards = is_array($fallbackEventCards) ? $fallbackEventCards : [];
    $eventCards = isset($section->items) && is_array($section->items) ? $section->items : $fallbackEventCards;
@endphp

<section class="theme-section theme-section-events">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <h2 class="text-4xl font-black tracking-tight text-[#1d4ed8]">
                {{ $heading }}
            </h2>
        </div>
    @endisset

    <div
        class="theme-content-pathway mx-auto mt-6 grid max-w-5xl gap-4 px-6 pb-14 md:grid-cols-3"
    >
        @forelse ($eventCards as $eventCard)
            @php
                $eventSignal = is_array($eventCard) && is_scalar($eventCard['signal'] ?? null) ? (string) $eventCard['signal'] : '';
                $eventTitle = is_array($eventCard) && is_scalar($eventCard['title'] ?? null) ? (string) $eventCard['title'] : '';
                $eventSummary = is_array($eventCard) && is_scalar($eventCard['summary'] ?? null) ? (string) $eventCard['summary'] : '';
                $eventDate = is_array($eventCard) && is_scalar($eventCard['date'] ?? null) ? (string) $eventCard['date'] : '';
                $eventUrl = is_array($eventCard) && is_scalar($eventCard['url'] ?? null) ? (string) $eventCard['url'] : null;
                $eventLabel = $loop->first
                    ? ($eventsAvailable ? __('capell-theme-education::generic.events_connected') : __('capell-theme-education::generic.events_static'))
                    : $eventSignal;
            @endphp

            @if ($eventLabel !== '' && $eventTitle !== '' && $eventSummary !== '')
                <article
                    class="rounded-xl border border-indigo-200 bg-white p-5"
                >
                    <p
                        class="text-xs font-black tracking-widest text-[#1d4ed8]"
                    >
                        {{ $eventLabel }}
                    </p>
                    <h3 class="mt-2 text-lg font-black">
                        @if ($eventUrl)
                            <a href="{{ $eventUrl }}" class="no-underline hover:text-[#1d4ed8]">
                                {{ $eventTitle }}
                            </a>
                        @else
                            {{ $eventTitle }}
                        @endif
                    </h3>
                    @if ($eventDate !== '')
                        <p class="mt-2 text-xs font-black tracking-widest text-[#0f766e] uppercase">
                            {{ $eventDate }}
                        </p>
                    @endif
                    <p class="mt-2 text-sm text-stone-600">
                        {{ $eventSummary }}
                    </p>
                </article>
            @endif
        @empty
            <article class="rounded-xl border border-dashed border-indigo-200 bg-white p-5 md:col-span-3">
                <h3 class="text-lg font-black">
                    {{ __('capell-theme-education::generic.events_empty_title') }}
                </h3>
                <p class="mt-2 text-sm text-stone-600">
                    {{ __('capell-theme-education::generic.events_empty_summary') }}
                </p>
            </article>
        @endforelse
    </div>
</section>
