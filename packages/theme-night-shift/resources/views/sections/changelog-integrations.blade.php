@php
    $entries = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-night-shift::sections.events.planning_title'), 'summary' => __('capell-theme-night-shift::sections.events.planning_summary'), 'meta' => __('capell-theme-night-shift::sections.events.planning_meta')],
        ['title' => __('capell-theme-night-shift::sections.events.workshop_title'), 'summary' => __('capell-theme-night-shift::sections.events.workshop_summary'), 'meta' => __('capell-theme-night-shift::sections.events.workshop_meta')],
        ['title' => __('capell-theme-night-shift::sections.events.systems_title'), 'summary' => __('capell-theme-night-shift::sections.events.systems_summary'), 'meta' => __('capell-theme-night-shift::sections.events.systems_meta')],
    ]));
@endphp

<section
    id="changelog-integrations"
    class="dps-section"
>
    <div class="dps-section-inner">
        <p class="dps-eyebrow">
            {{ __('capell-theme-night-shift::sections.events.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-night-shift::sections.events.heading')) }}
        </h2>

        <div
            class="dps-changelog"
            style="margin-top: clamp(2rem, 4vw, 3rem)"
        >
            @foreach ($entries as $entry)
                @php
                    $entryImage = data_get($entry, 'image', data_get($entry, 'imageUrl'));
                @endphp

                <article class="dps-changelog-row">
                    <span class="dps-changelog-date">
                        {{ data_get($entry, 'meta', data_get($entry, 'category', '')) }}
                    </span>
                    <div
                        style="
                            display: flex;
                            gap: 1rem;
                            align-items: flex-start;
                        "
                    >
                        @if (filled($entryImage))
                            <img
                                src="{{ $entryImage }}"
                                alt="{{ data_get($entry, 'imageAlt', data_get($entry, 'title', '')) }}"
                                loading="lazy"
                                decoding="async"
                                class="dps-integration-thumb"
                            />
                        @endif

                        <div>
                            <h3>
                                {{ data_get($entry, 'title', data_get($entry, 'name', '')) }}
                            </h3>
                            <p>
                                {{ data_get($entry, 'summary', data_get($entry, 'description', '')) }}
                            </p>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
