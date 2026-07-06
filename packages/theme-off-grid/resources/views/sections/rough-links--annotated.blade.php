{{--
    rough-links-directory, `annotated` variant: the same plain link rows
    with a leading directory-style index numeral (rwi-index-numeral, shared
    with irregular-index) — for placements where the raw link list benefits
    from an explicit count, e.g. a long submission-route directory.
--}}

@php
    $items = data_get($section, 'items', []);
@endphp

<section
    id="rough-links"
    class="rwi-section"
    data-widget="rough-links-directory"
    data-variant="annotated"
>
    <div class="rwi-section-inner">
        <span class="rwi-index-tag">03</span>
        <p class="rwi-kicker">
            {{ __('capell-theme-off-grid::sections.links.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-off-grid::sections.links.heading')) }}
        </h2>
        <p class="rwi-lede">
            {{ data_get($section, 'summary', __('capell-theme-off-grid::sections.links.summary')) }}
        </p>

        <div
            class="rwi-link-list rwi-link-list-annotated"
            style="margin-top: 2rem"
        >
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <div class="rwi-link-row rwi-link-row-annotated">
                    <span
                        class="rwi-index-numeral"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <div>
                        @if (filled($itemUrl))
                            <a href="{{ $itemUrl }}">
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            </a>
                        @else
                            <strong>
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            </strong>
                        @endif
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
