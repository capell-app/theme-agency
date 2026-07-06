@php
    // §0.2 live-state policy: `state` is an editorially set payload value
    // (active|hidden), never polled or client-detected.
    $state = data_get($section, 'state', 'hidden');
    $isActive = $state === 'active';
    $heading = data_get($section, 'heading', __('capell-theme-ink-press::sections.breaking.heading'));
    $url = data_get($section, 'url', '#top-stories');
@endphp

@if ($isActive)
    <section
        id="breaking-news-ribbon"
        class="dnews-ribbon dnews-ribbon-compact"
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
            </a>
        </div>
    </section>
@endif
