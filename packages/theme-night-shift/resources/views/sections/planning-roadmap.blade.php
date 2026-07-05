@php
    $heading = data_get($section, 'heading', __('capell-theme-night-shift::sections.updates.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-night-shift::sections.updates.summary'));
    $lanes = data_get($section, 'items', [
        ['title' => __('capell-theme-night-shift::sections.updates.now_title'), 'summary' => __('capell-theme-night-shift::sections.updates.now_summary')],
        ['title' => __('capell-theme-night-shift::sections.updates.next_title'), 'summary' => __('capell-theme-night-shift::sections.updates.next_summary')],
        ['title' => __('capell-theme-night-shift::sections.updates.later_title'), 'summary' => __('capell-theme-night-shift::sections.updates.later_summary')],
    ]);
    $label = data_get($section, 'label', __('capell-theme-night-shift::sections.updates.button'));
    $url = data_get($section, 'url', '/');
@endphp

<section
    id="planning-roadmap"
    class="dps-section dps-section-raised"
>
    <div class="dps-section-inner">
        <div class="dps-heading-row">
            <div>
                <p class="dps-eyebrow">
                    {{ __('capell-theme-night-shift::sections.updates.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="dps-lede">{{ $summary }}</p>
            </div>
            <a
                class="dps-button dps-button-secondary"
                href="{{ $url }}"
            >
                {{ $label }}
            </a>
        </div>

        <div class="dps-lanes">
            @foreach ($lanes as $lane)
                <article class="dps-lane">
                    <span class="dps-lane-label">
                        {{ data_get($lane, 'title', data_get($lane, 'name', '')) }}
                    </span>
                    <p>{{ data_get($lane, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
