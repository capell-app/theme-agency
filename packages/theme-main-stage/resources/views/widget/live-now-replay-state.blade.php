@php
    $state = (string) ($widget->getMeta('state') ?? 'upcoming');
    $variant = (string) ($widget->getMeta('variant') ?? 'banner');
    $heading = (string) ($widget->getMeta('heading') ?? '');
    $summary = (string) ($widget->getMeta('summary') ?? '');
    $actionLabel = (string) ($widget->getMeta('actionLabel') ?? '');
    $actionUrl = (string) ($widget->getMeta('actionUrl') ?? '#');
    $stateLabel = __('capell-theme-main-stage::generic.live_state.' . (in_array($state, ['upcoming', 'live', 'replay'], true) ? $state : 'upcoming'));
@endphp

{{--
    `live-now-replay-state` — Part 2 §E signature widget, and the exact
    example §0.2 names verbatim: "'Live now / replay / closed' are
    editorially toggled payload states, never detected." `$state` above is
    read straight off `Widget->meta['state']` (an editor-set payload value:
    `upcoming` | `live` | `replay`) — there is no timer, no server request,
    and no comparison against the current time anywhere in this view. An
    editor is the only thing that ever changes which of the three states
    renders; the page is exactly as html-cache-safe as any other static
    payload-driven widget.

    Two variants: `banner` (full-width state strip, typically placed near
    the top of a page during the event) and `inline` (a compact state chip
    for reuse inside a card, e.g. the agenda or archive widgets).
--}}
<section
    id="live-state"
    class="mst-shell mst-live-state mst-live-state--{{ $variant === 'inline' ? 'inline' : 'banner' }} mst-live-state-{{ $state }}"
>
    <div class="mst-section-inner mst-live-state-inner">
        <span class="mst-live-state-badge">
            <span
                class="mst-live-state-dot"
                aria-hidden="true"
            ></span>
            {{ $stateLabel }}
        </span>

        @if ($heading !== '')
            <h2>{{ $heading }}</h2>
        @endif

        @if ($summary !== '')
            <p class="mst-lede">{{ $summary }}</p>
        @endif

        @if ($actionLabel !== '')
            <a
                href="{{ $actionUrl }}"
                class="mst-button"
                >{{ $actionLabel }}</a
            >
        @endif
    </div>
</section>
