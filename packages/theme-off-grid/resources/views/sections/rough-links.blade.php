@php
    $items = data_get($section, 'items', []);
@endphp

<section
    id="rough-links"
    class="rwi-section"
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
            class="rwi-link-list"
            style="margin-top: 2rem"
        >
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <div class="rwi-link-row">
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
            @endforeach
        </div>
    </div>
</section>
