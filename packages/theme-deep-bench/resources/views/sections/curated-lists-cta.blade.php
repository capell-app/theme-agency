{{--
    curated-lists-cta — a focused conversion band that closes the roster
    with a curated-list pick plus a direct submit-your-portfolio call to
    action, distinct from the standalone curated-lists browse section: this
    one pairs a single featured list with the CTA rather than a browse grid.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-deep-bench::sections.lists_cta.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-deep-bench::sections.lists_cta.summary'));
    $featuredList = data_get($section, 'featuredList', [
        'title' => __('capell-theme-deep-bench::sections.lists_cta.featured_title'),
        'summary' => __('capell-theme-deep-bench::sections.lists_cta.featured_summary'),
        'meta' => __('capell-theme-deep-bench::sections.lists_cta.featured_meta'),
    ]);
    $ctaLabel = data_get($section, 'label', __('capell-theme-deep-bench::sections.lists_cta.button'));
    $ctaUrl = data_get($section, 'url', '#curated-lists-cta');
@endphp

<section
    id="curated-lists-cta"
    class="pfd-section pfd-section-night"
>
    <div class="pfd-section-inner pfd-lists-cta-inner">
        <div>
            <p class="pfd-eyebrow">
                {{ data_get($section, 'eyebrow', __('capell-theme-deep-bench::sections.lists_cta.eyebrow')) }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="pfd-lede">{{ $summary }}</p>

            <div class="pfd-actions">
                <a
                    class="pfd-button"
                    href="{{ $ctaUrl }}"
                >
                    {{ $ctaLabel }}
                </a>
            </div>
        </div>

        <article class="pfd-lists-cta-card">
            <h3>{{ data_get($featuredList, 'title', '') }}</h3>
            <p>{{ data_get($featuredList, 'summary', '') }}</p>
            <span class="pfd-rail-meta">
                {{ data_get($featuredList, 'meta', '') }}
            </span>
        </article>
    </div>
</section>
