@php
    $items = data_get($section, 'items', data_get($section, 'stories', []));
@endphp

<section
    id="archive-dates"
    class="rwi-section"
>
    <div class="rwi-section-inner">
        <span class="rwi-index-tag">05</span>
        <p class="rwi-kicker">
            {{ __('capell-theme-raw-index::sections.dates.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-raw-index::sections.dates.heading')) }}
        </h2>
        <p class="rwi-lede">
            {{ data_get($section, 'summary', __('capell-theme-raw-index::sections.dates.summary')) }}
        </p>

        <div
            class="rwi-ledger"
            style="margin-top: 2rem"
        >
            @foreach ($items as $item)
                <div class="rwi-ledger-row">
                    <span class="rwi-ledger-date">
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </span>
                    <p class="rwi-meta">
                        {{ data_get($item, 'meta', data_get($item, 'category', '')) }}
                    </p>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
