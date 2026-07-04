@php
    $heading = data_get($section, 'heading', __('capell-theme-raw-index::sections.index.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-raw-index::sections.index.summary'));
    $items = data_get($section, 'items', []);
@endphp

<section
    id="irregular-index"
    class="rwi-section"
>
    <div class="rwi-section-inner">
        <span class="rwi-index-tag">02</span>
        <p class="rwi-kicker">
            {{ __('capell-theme-raw-index::sections.index.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="rwi-lede">{{ $summary }}</p>

        <div
            class="rwi-index-list"
            style="margin-top: 2rem"
        >
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <article class="rwi-index-row">
                    <span
                        class="rwi-index-numeral"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <div>
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt ?: data_get($item, 'title', data_get($item, 'name', '')) }}"
                                loading="lazy"
                                decoding="async"
                                class="rwi-index-thumb"
                            />
                        @endif

                        <p class="rwi-meta">
                            {{ data_get($item, 'meta', __('capell-theme-raw-index::sections.index.default_meta')) }}
                        </p>
                        <h3>
                            @if (filled($itemUrl))
                                <a
                                    class="rwi-title-link"
                                    href="{{ $itemUrl }}"
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
                        <p class="rwi-index-note">
                            {{ data_get($item, 'care_note', __('capell-theme-raw-index::sections.index.care_note')) }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
