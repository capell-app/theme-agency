@php
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-night-shift::sections.events.heading'));
    $items = is_array($widget->getMeta('items')) ? $widget->getMeta('items') : [];
@endphp

{{--
    `dps-shell` is carried on this widget's root `<section>` (rather than a
    page-level wrapper, which no longer exists now that this theme is
    definition-only — see `NightShiftThemeServiceProvider`) so the
    `--dps-*` token derivations and
    `@container dps-tokens style(--theme-*: ...)` rules in
    `resources/css/theme-night-shift.css` still resolve here, scoped to this
    widget instead of leaking onto the shared `.site-theme-shell` wrapper
    every other layout-native theme also renders inside.
--}}
<section
    id="changelog-integrations"
    class="dps-shell dps-section"
>
    <div class="dps-section-inner">
        <p class="dps-eyebrow">
            {{ __('capell-theme-night-shift::sections.events.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>

        <div
            class="dps-changelog"
            style="margin-top: clamp(2rem, 4vw, 3rem)"
        >
            @foreach ($items as $entry)
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
