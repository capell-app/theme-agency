@php
    $heading = data_get($section, 'heading', __('capell-theme-editorial-serif::sections.features.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-editorial-serif::sections.features.summary'));
    $items = collect(data_get($section, 'items', []));
@endphp

<section
    id="features"
    class="eser-section eser-section-wide"
>
    <div class="eser-section-inner">
        <p class="eser-eyebrow">
            {{ __('capell-theme-editorial-serif::sections.features.eyebrow') }}
        </p>
        <h2>{{ $heading }}</h2>
        <hr class="eser-heading-rule" />
        @if (filled($summary))
            <p class="eser-summary">{{ $summary }}</p>
        @endif

        @if ($items->isNotEmpty())
            <div class="eser-feature-grid">
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    @endphp

                    <article>
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                width="800"
                                height="600"
                                loading="lazy"
                                decoding="async"
                                class="eser-feature-media"
                            />
                        @endif

                        <h3 class="eser-feature-title">
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
                        <p class="eser-feature-body">
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
