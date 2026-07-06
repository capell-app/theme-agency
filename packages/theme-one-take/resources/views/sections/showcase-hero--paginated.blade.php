{{--
    showcase-hero-dossier "--paginated" variant (mechanic: dossier pages).
    Each capture becomes its own scroll-snapped document page instead of a
    flat spread: `scroll-snap-type: y mandatory` on the track and
    `scroll-snap-align: start` per page (pure CSS, §0.1 — no JS scroll
    hijacking). Where the `@view-transition` CSS API is supported, moving
    from page to page cross-fades the folio number; unsupported browsers
    just get the instant scroll-snap stop as the fallback (§0.8).
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

        <div
            class="ops-dossier-track"
            role="group"
            aria-label="{{ __('capell-theme-one-take::sections.featured.track_label') }}"
        >
            @foreach ($items as $index => $item)
                <article
                    class="ops-dossier-track-page"
                    style="view-transition-name: ops-dossier-folio-{{ $index + 1 }}"
                >
                    <div class="ops-capture-frame">
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
                            <p class="ops-dossier-folio">
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
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
