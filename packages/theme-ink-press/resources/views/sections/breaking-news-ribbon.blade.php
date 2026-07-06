@php
    // §0.2 live-state policy: `state` is an editorially set payload value
    // (active|hidden), never polled or client-detected. A missing/unknown
    // state defaults to hidden so a page never shows a stale-looking ribbon
    // by accident.
    $state = data_get($section, 'state', 'hidden');
    $isActive = $state === 'active';
    $heading = data_get($section, 'heading', __('capell-theme-ink-press::sections.breaking.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-ink-press::sections.breaking.summary'));
    $url = data_get($section, 'url', '#top-stories');
    $label = data_get($section, 'label', __('capell-theme-ink-press::sections.breaking.label'));
@endphp

@if ($isActive)
    <section
        id="breaking-news-ribbon"
        class="dnews-ribbon"
        data-state="active"
        aria-label="{{ __('capell-theme-ink-press::sections.breaking.aria_label') }}"
    >
        <div class="dnews-section-inner dnews-ribbon-inner">
            <p class="dnews-ribbon-tag">
                {{ __('capell-theme-ink-press::sections.breaking.tag') }}
            </p>
            <a
                class="dnews-ribbon-link"
                href="{{ $url }}"
            >
                <strong>{{ $heading }}</strong>
                <span>{{ $summary }}</span>
            </a>
            <span
                class="dnews-ribbon-cta"
                aria-hidden="true"
                >{{ $label }}</span
            >
        </div>
    </section>
@endif
