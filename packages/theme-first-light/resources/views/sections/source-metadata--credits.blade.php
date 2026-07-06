{{--
    source-metadata, `credits` variant realising source-metadata-credits: the
    same maker/source/filed-under facts as the default label/value rows, but
    laid out as a compact inline credit strip (a single line of small-caps
    facts separated by middot) suited to sitting directly under a lightbox
    caption or a detail-page hero rather than as its own full-width block.
--}}

@php
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
@endphp

<section
    id="source-metadata"
    class="mcf-section"
    data-widget="source-metadata-credits"
    data-variant="credits"
>
    <div class="mcf-section-inner">
        <p class="mcf-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-first-light::sections.metadata.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-first-light::sections.metadata.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="mcf-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        @if ($items->isNotEmpty())
            <ul class="mcf-credit-strip">
                @foreach ($items as $item)
                    <li class="mcf-credit-item">
                        <span class="mcf-meta">
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </span>
                        <span class="mcf-tiny">
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
