{{--
    one-page-grid-showcase "--dense" variant (mechanic: dossier pages). Same
    capped payload (50 items, §0.3) rendered as a running dossier index —
    numbered rows rather than a card grid — for galleries whose archive has
    grown past a comfortable card wall. Folio numbers still anchor the
    document metaphor; the tag becomes an inline marginal note.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-one-take::sections.stories.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-one-take::sections.stories.summary'));
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-one-take::sections.stories.product_title'), 'summary' => __('capell-theme-one-take::sections.stories.product_summary'), 'meta' => __('capell-theme-one-take::sections.stories.product_meta')],
        ['title' => __('capell-theme-one-take::sections.stories.design_title'), 'summary' => __('capell-theme-one-take::sections.stories.design_summary'), 'meta' => __('capell-theme-one-take::sections.stories.design_meta')],
        ['title' => __('capell-theme-one-take::sections.stories.advice_title'), 'summary' => __('capell-theme-one-take::sections.stories.advice_summary'), 'meta' => __('capell-theme-one-take::sections.stories.advice_meta')],
    ]))->take(50);
    $startingFolio = (int) data_get($section, 'startingFolio', 1);
@endphp

<section
    id="one-page-grid"
    class="ops-section"
>
    <div class="ops-section-inner">
        <p class="ops-kicker">
            {{ __('capell-theme-one-take::sections.stories.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="ops-lede">{{ $summary }}</p>

        <ol class="ops-dossier-index">
            @foreach ($items as $index => $item)
                <li class="ops-dossier-index-row">
                    <span
                        class="ops-dossier-folio"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) ($startingFolio + $index), 2, '0', STR_PAD_LEFT) }}
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
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </span>
                    <span class="ops-dossier-index-tag">
                        {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-one-take::sections.stories.default_meta'))) }}
                    </span>
                </li>
            @endforeach
        </ol>
    </div>
</section>
