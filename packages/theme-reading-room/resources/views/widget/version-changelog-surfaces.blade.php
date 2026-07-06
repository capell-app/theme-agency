@php
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-reading-room::sections.changelog.heading'));
    $summary = (string) ($widget->getMeta('summary') ?? '');
    $entries = is_array($widget->getMeta('entries')) ? $widget->getMeta('entries') : [];
    $variant = (string) ($widget->getMeta('variant') ?? 'rows');
@endphp

{{--
    `version-changelog-surfaces` — breaking changes are flagged distinctly
    from minor/patch entries via a `changeType` payload key
    (breaking|minor|patch), never inferred client-side.

    Two variants:
    - `rows` (default): a flat list of version rows, each with a flag chip.
    - `timeline`: the same entries rendered against a left rule + dot
      timeline spine (`.rr-changelog-timeline`).
--}}
<section
    id="version-changelog-surfaces"
    class="rr-shell rr-section rr-section-raised"
>
    <div class="rr-section-inner">
        <p class="rr-eyebrow">
            {{ __('capell-theme-reading-room::sections.changelog.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>

        @if ($summary !== '')
            <p class="rr-lede">{{ $summary }}</p>
        @endif

        <div
            class="{{ $variant === 'timeline' ? 'rr-changelog-timeline' : 'rr-changelog' }}"
            style="margin-top: clamp(1.5rem, 3vw, 2.25rem)"
        >
            @foreach ($entries as $entry)
                @php
                    $changeType = (string) data_get($entry, 'changeType', 'patch');
                    $flagClass = match ($changeType) {
                        'breaking' => 'rr-changelog-flag-breaking',
                        'minor' => 'rr-changelog-flag-minor',
                        default => 'rr-changelog-flag-patch',
                    };
                    $flagLabel = match ($changeType) {
                        'breaking' => __('capell-theme-reading-room::sections.changelog.flag_breaking'),
                        'minor' => __('capell-theme-reading-room::sections.changelog.flag_minor'),
                        default => __('capell-theme-reading-room::sections.changelog.flag_patch'),
                    };
                @endphp

                <article class="rr-changelog-row">
                    <span class="rr-changelog-version">
                        {{ data_get($entry, 'version', '') }}
                    </span>

                    <div>
                        <span
                            class="rr-changelog-flag {{ $flagClass }}"
                            >{{ $flagLabel }}</span
                        >
                        <h3 style="margin-top: 0.4rem">
                            {{ data_get($entry, 'title', '') }}
                        </h3>
                        <p>{{ data_get($entry, 'summary', '') }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
