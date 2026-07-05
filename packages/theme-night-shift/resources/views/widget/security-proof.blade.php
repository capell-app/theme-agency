@php
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-night-shift::sections.authors.heading'));
    $summary = (string) ($widget->getMeta('summary') ?? __('capell-theme-night-shift::sections.authors.summary'));
    $items = is_array($widget->getMeta('items')) ? $widget->getMeta('items') : [];
    $badges = [
        __('capell-theme-night-shift::sections.authors.badge_soc2'),
        __('capell-theme-night-shift::sections.authors.badge_sso'),
        __('capell-theme-night-shift::sections.authors.badge_gdpr'),
        __('capell-theme-night-shift::sections.authors.badge_uptime'),
    ];
@endphp

{{--
    `dps-shell` is carried on this widget's root `<section>` for the same
    reason documented in `resources/views/widget/changelog-integrations.blade.php`.
--}}
<section
    id="security-proof"
    class="dps-shell dps-section dps-section-field"
>
    <div class="dps-section-inner">
        <p class="dps-eyebrow">
            {{ __('capell-theme-night-shift::sections.authors.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="dps-lede">{{ $summary }}</p>

        <div class="dps-badge-row">
            @foreach ($badges as $badge)
                <span class="dps-badge">{{ $badge }}</span>
            @endforeach
        </div>

        <div
            class="dps-grid"
            style="margin-top: clamp(2rem, 4vw, 3rem)"
        >
            @foreach ($items as $item)
                <article class="dps-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
