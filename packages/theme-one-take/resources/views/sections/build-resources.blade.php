{{--
    build-resources-cta (Wave 4c signature widget, mechanic: dossier pages).
    Reads as the dossier's closing chapter — a colophon-style index of build
    guides with running folio numbers, ending in a "read the next chapter"
    call to action, rather than a plain resources list. Default variant
    keeps every entry full width; "--split" pairs a lead colophon card
    against the remaining guides for pages with a hero-worthy first guide.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-one-take::sections.authors.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-one-take::sections.authors.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-one-take::sections.authors.design_title'), 'summary' => __('capell-theme-one-take::sections.authors.design_summary')],
        ['title' => __('capell-theme-one-take::sections.authors.advice_title'), 'summary' => __('capell-theme-one-take::sections.authors.advice_summary')],
        ['title' => __('capell-theme-one-take::sections.authors.company_title'), 'summary' => __('capell-theme-one-take::sections.authors.company_summary')],
    ]);
@endphp

<section
    id="build-resources"
    class="ops-section ops-section-field"
>
    <div class="ops-section-inner">
        <p class="ops-kicker">
            {{ __('capell-theme-one-take::sections.authors.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="ops-lede">{{ $summary }}</p>
        <ol class="ops-dossier-colophon">
            @foreach ($items as $index => $item)
                <li class="ops-dossier-index-row">
                    <span
                        class="ops-dossier-folio"
                        aria-hidden="true"
                    >
                        {{ __('capell-theme-one-take::sections.authors.chapter', ['number' => $index + 1]) }}
                    </span>
                    <span class="ops-dossier-index-body">
                        <h3>
                            @if (filled(data_get($item, 'url', data_get($item, 'href'))))
                                <a
                                    class="ops-title-link"
                                    href="{{ data_get($item, 'url', data_get($item, 'href')) }}"
                                >
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                </a>
                            @else
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            @endif
                        </h3>
                        <p>{{ data_get($item, 'summary', '') }}</p>
                    </span>
                </li>
            @endforeach
        </ol>

        @if (filled(data_get($section, 'ctaUrl', data_get($section, 'cta_url'))))
            <div class="ops-actions ops-dossier-colophon-cta">
                <a
                    class="ops-button"
                    href="{{ data_get($section, 'ctaUrl', data_get($section, 'cta_url')) }}"
                >
                    {{ data_get($section, 'ctaLabel', data_get($section, 'cta_label', __('capell-theme-one-take::sections.authors.cta_label'))) }}
                </a>
            </div>
        @endif
    </div>
</section>
