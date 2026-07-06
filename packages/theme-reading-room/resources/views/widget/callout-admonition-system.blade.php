@php
    $admonitions = is_array($widget->getMeta('admonitions')) ? $widget->getMeta('admonitions') : [];
    $variant = (string) ($widget->getMeta('variant') ?? 'block');
@endphp

{{--
    `callout-admonition-system` — note/warning/danger/tip boxes, visually
    distinct via the `--rr-note`/`--rr-warning`/`--rr-danger`/`--rr-tip` CSS
    custom properties (never hardcoded hex — see `ThemeDarkModeParityTest`),
    each derived from the theme's accent/foreground tokens so dark-mode
    presets stay legible without a separate dark palette.

    Two variants:
    - `block` (default): the full block layout with a labelled heading row.
    - `inline`: a compact single-line treatment for dense reference tables
      (`.rr-admonition-inline`).
--}}
<section
    id="callout-admonition-system"
    class="rr-shell rr-section rr-section-field"
>
    <div class="rr-section-inner">
        <div style="display: grid; gap: 0.85rem">
            @foreach ($admonitions as $admonition)
                @php
                    $kind = (string) data_get($admonition, 'kind', 'note');
                    $kindClass = match ($kind) {
                        'tip' => 'rr-admonition-tip',
                        'warning' => 'rr-admonition-warning',
                        'danger' => 'rr-admonition-danger',
                        default => 'rr-admonition-note',
                    };
                    $kindLabel = match ($kind) {
                        'tip' => __('capell-theme-reading-room::sections.admonitions.label_tip'),
                        'warning' => __('capell-theme-reading-room::sections.admonitions.label_warning'),
                        'danger' => __('capell-theme-reading-room::sections.admonitions.label_danger'),
                        default => __('capell-theme-reading-room::sections.admonitions.label_note'),
                    };
                @endphp

                <div
                    class="rr-admonition {{ $kindClass }} {{ $variant === 'inline' ? 'rr-admonition-inline' : '' }}"
                >
                    <p class="rr-admonition-label">{{ $kindLabel }}</p>
                    <p>{{ data_get($admonition, 'body', '') }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
