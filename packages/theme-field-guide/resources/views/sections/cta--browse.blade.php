{{--
    collection-cta-browse (Wave 4c signature widget #5, browse variant): the
    closing CTA also surfaces a short row of collection shortcuts (facet
    quick-links), so the conversion moment doubles as one more way back into
    the taxonomy grid rather than a dead end.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-field-guide::sections.cta.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-field-guide::sections.cta.summary'));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'label', __('capell-theme-field-guide::sections.cta.primary_label')),
                'url' => data_get($section, 'url', '#newsletter'),
                'style' => 'primary',
            ],
            [
                'label' => __('capell-theme-field-guide::sections.cta.secondary_label'),
                'url' => '#taxonomy-grid-browser',
                'style' => 'secondary',
            ],
        ]);
    }

    $browseCollections = collect(data_get($section, 'collections', [
        ['label' => __('capell-theme-field-guide::sections.cta.browse_type'), 'url' => '#taxonomy-navigation'],
        ['label' => __('capell-theme-field-guide::sections.cta.browse_style'), 'url' => '#taxonomy-navigation'],
        ['label' => __('capell-theme-field-guide::sections.cta.browse_colour'), 'url' => '#taxonomy-navigation'],
        ['label' => __('capell-theme-field-guide::sections.cta.browse_industry'), 'url' => '#taxonomy-navigation'],
    ]))->filter(fn (mixed $collection): bool => filled(data_get($collection, 'label')))->values();
@endphp

<section
    id="cta"
    class="fga-section fga-section-dark"
    data-widget="collection-cta-browse"
    data-variant="browse"
>
    <div class="fga-section-inner">
        <div class="fga-section-head-copy">
            <p class="fga-kicker">
                {{ __('capell-theme-field-guide::sections.cta.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="fga-lede">{{ $summary }}</p>
        </div>
        <div class="fga-actions">
            @foreach ($actions as $action)
                <a
                    class="fga-button {{ data_get($action, 'style') === 'secondary' ? 'fga-button-secondary' : '' }}"
                    href="{{ data_get($action, 'url', '/') }}"
                >
                    {{ data_get($action, 'label') }}
                </a>
            @endforeach
        </div>

        @if ($browseCollections->isNotEmpty())
            <ul class="fga-cta-browse-row">
                @foreach ($browseCollections as $collection)
                    <li>
                        <a
                            class="fga-cta-browse-link"
                            href="{{ data_get($collection, 'url', '#taxonomy-grid-browser') }}"
                        >
                            {{ data_get($collection, 'label') }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
