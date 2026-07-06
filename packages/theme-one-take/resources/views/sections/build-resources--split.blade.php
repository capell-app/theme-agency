{{--
    build-resources-cta "--split" variant (mechanic: dossier pages). The
    first guide becomes a lead colophon card (title, summary, and its own
    "read chapter" link) beside the remaining guides in the running index,
    for a build-resources section that wants one guide to read as the
    dossier's featured closing chapter rather than a flat list.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-one-take::sections.authors.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-one-take::sections.authors.summary'));
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-one-take::sections.authors.design_title'), 'summary' => __('capell-theme-one-take::sections.authors.design_summary')],
        ['title' => __('capell-theme-one-take::sections.authors.advice_title'), 'summary' => __('capell-theme-one-take::sections.authors.advice_summary')],
        ['title' => __('capell-theme-one-take::sections.authors.company_title'), 'summary' => __('capell-theme-one-take::sections.authors.company_summary')],
    ]));
    $lead = $items->first();
    $rest = $items->slice(1)->values();
@endphp

<section
    id="build-resources"
    class="ops-section ops-section-field"
>
    <div class="ops-section-inner ops-split ops-dossier-colophon-split">
        <div class="ops-dossier-colophon-lead">
            <p class="ops-kicker">
                {{ __('capell-theme-one-take::sections.authors.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="ops-lede">{{ $summary }}</p>

            @if ($lead)
                <article class="ops-card ops-dossier-colophon-lead-card">
                    <span
                        class="ops-dossier-folio"
                        aria-hidden="true"
                    >
                        {{ __('capell-theme-one-take::sections.authors.chapter', ['number' => 1]) }}
                    </span>
                    <h3>
                        @if (filled(data_get($lead, 'url', data_get($lead, 'href'))))
                            <a
                                class="ops-title-link"
                                href="{{ data_get($lead, 'url', data_get($lead, 'href')) }}"
                            >
                                {{ data_get($lead, 'title', data_get($lead, 'name', '')) }}
                            </a>
                        @else
                            {{ data_get($lead, 'title', data_get($lead, 'name', '')) }}
                        @endif
                    </h3>
                    <p>{{ data_get($lead, 'summary', '') }}</p>
                </article>
            @endif

            @if (filled(data_get($section, 'ctaUrl', data_get($section, 'cta_url'))))
                <div class="ops-actions">
                    <a
                        class="ops-button"
                        href="{{ data_get($section, 'ctaUrl', data_get($section, 'cta_url')) }}"
                    >
                        {{ data_get($section, 'ctaLabel', data_get($section, 'cta_label', __('capell-theme-one-take::sections.authors.cta_label'))) }}
                    </a>
                </div>
            @endif
        </div>

        <ol class="ops-dossier-colophon">
            @foreach ($rest as $index => $item)
                <li class="ops-dossier-index-row">
                    <span
                        class="ops-dossier-folio"
                        aria-hidden="true"
                    >
                        {{ __('capell-theme-one-take::sections.authors.chapter', ['number' => $index + 2]) }}
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
    </div>
</section>
