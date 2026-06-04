@php
    use Capell\Core\Facades\CapellCore;

    $eventsAvailable ??= CapellCore::isPackageInstalled('capell-app/events');
    $eventCards = __('capell-theme-education::generic.event_cards');
    $eventCards = is_array($eventCards) ? $eventCards : [];
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
        @foreach ($eventCards as $eventCard)
            @php
                $eventSignal = is_array($eventCard) && is_scalar($eventCard['signal'] ?? null) ? (string) $eventCard['signal'] : '';
                $eventTitle = is_array($eventCard) && is_scalar($eventCard['title'] ?? null) ? (string) $eventCard['title'] : '';
                $eventSummary = is_array($eventCard) && is_scalar($eventCard['summary'] ?? null) ? (string) $eventCard['summary'] : '';
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
                        {{ $eventTitle }}
                    </h3>
                    <p class="mt-2 text-sm text-stone-600">
                        {{ $eventSummary }}
                    </p>
                </article>
            @endif
        @endforeach
    </div>
</section>
