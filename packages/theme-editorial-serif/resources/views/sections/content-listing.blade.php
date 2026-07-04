@php
    $heading = data_get($section, 'heading', __('capell-theme-editorial-serif::sections.content_listing.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-editorial-serif::sections.content_listing.summary'));
    $items = collect(data_get($section, 'items', data_get($section, 'posts', [])));
@endphp

<section
    id="content-listing"
    class="eser-section"
>
    <div class="eser-section-inner">
        <p class="eser-eyebrow">
            {{ __('capell-theme-editorial-serif::sections.content_listing.eyebrow') }}
        </p>
        <h2>{{ $heading }}</h2>
        <hr class="eser-heading-rule" />
        @if (filled($summary))
            <p class="eser-summary">{{ $summary }}</p>
        @endif

        @if ($items->isNotEmpty())
            <div class="eser-index">
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    @endphp

                    <article class="eser-index-row">
                        <span
                            class="eser-index-numeral"
                            aria-hidden="true"
                        >
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <div>
                            @if (filled($itemImage))
                                <img
                                    src="{{ $itemImage }}"
                                    alt="{{ $itemAlt }}"
                                    width="1400"
                                    height="600"
                                    loading="lazy"
                                    decoding="async"
                                    class="eser-index-figure"
                                />
                            @endif

                            <h3 class="eser-index-title">
                                @if (filled($itemUrl))
                                    <a
                                        class="eser-title-link"
                                        href="{{ $itemUrl }}"
                                    >
                                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                    </a>
                                @else
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                @endif
                            </h3>
                            <p class="eser-index-dek">
                                {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
