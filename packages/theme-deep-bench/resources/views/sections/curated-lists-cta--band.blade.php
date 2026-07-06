{{--
    curated-lists-cta--band — same featured-list + submit CTA pairing as the
    default view, on the standard light field surface as a slimmer band
    instead of the dark closing section, for pages that already end on a
    dark surface elsewhere.
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
    class="pfd-section pfd-section-field pfd-lists-cta-band"
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
