{{--
    showcase-hero-dossier (Wave 4c signature widget, mechanic: dossier pages).
    Renders the featured one-pagers as a bound document's opening spread:
    a running page number per capture, a margin note beside each entry, and
    a folio/rule treatment borrowed from print dossiers rather than a plain
    card grid. This is the "default" (flat spread) variant; the "--paginated"
    variant additionally scroll-snaps each capture as its own document page
    (see §0.8: CSS scroll-snap only, no JS scroll-hijacking).
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-one-take::sections.featured.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-one-take::sections.featured.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-one-take::sections.featured.launch_title'), 'summary' => __('capell-theme-one-take::sections.featured.launch_summary'), 'note' => __('capell-theme-one-take::sections.featured.launch_note')],
        ['title' => __('capell-theme-one-take::sections.featured.essay_title'), 'summary' => __('capell-theme-one-take::sections.featured.essay_summary'), 'note' => __('capell-theme-one-take::sections.featured.essay_note')],
        ['title' => __('capell-theme-one-take::sections.featured.template_title'), 'summary' => __('capell-theme-one-take::sections.featured.template_summary'), 'note' => __('capell-theme-one-take::sections.featured.template_note')],
    ]);
@endphp

<section
    id="showcase-hero"
    class="ops-section ops-dossier"
    aria-label="{{ __('capell-theme-one-take::sections.featured.aria_label') }}"
>
    <div class="ops-section-inner">
        <p class="ops-kicker">
            {{ __('capell-theme-one-take::sections.featured.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="ops-lede">{{ $summary }}</p>

        <div class="ops-grid ops-grid-captures ops-dossier-spread">
            @foreach ($items as $index => $item)
                <article
                    class="ops-capture-frame ops-dossier-page"
                    style="--ops-folio: {{ $index + 1 }}"
                >
                    <div
                        class="ops-capture-chrome"
                        aria-hidden="true"
                    >
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                    @if (filled(data_get($item, 'image', data_get($item, 'imageUrl'))))
                        <img
                            src="{{ data_get($item, 'image', data_get($item, 'imageUrl')) }}"
                            alt="{{ data_get($item, 'imageAlt', data_get($item, 'title', '')) }}"
                            width="900"
                            height="1200"
                            loading="lazy"
                            decoding="async"
                            class="ops-capture-media"
                        />
                    @else
                        <div
                            class="ops-capture-media ops-capture-media-empty"
                            aria-hidden="true"
                        ></div>
                    @endif
                    <div class="ops-capture-body">
                        <p
                            class="ops-dossier-folio"
                            aria-hidden="true"
                        >
                            {{ __('capell-theme-one-take::sections.featured.folio', ['number' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT), 'total' => str_pad((string) count($items), 2, '0', STR_PAD_LEFT)]) }}
                        </p>
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
                        @if (filled(data_get($item, 'note')))
                            <p class="ops-dossier-margin-note">
                                <span aria-hidden="true">&mdash;</span>
                                {{ data_get($item, 'note') }}
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
