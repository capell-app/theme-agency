@php
    $heading = data_get($section, 'heading', __('capell-theme-editorial-serif::sections.author_profiles.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-editorial-serif::sections.author_profiles.summary'));
    $items = collect(data_get($section, 'items', data_get($section, 'authors', [])));
@endphp

<section
    id="author-profiles"
    class="eser-section"
>
    <div class="eser-section-inner">
        <p class="eser-eyebrow">
            {{ __('capell-theme-editorial-serif::sections.author_profiles.eyebrow') }}
        </p>
        <h2>{{ $heading }}</h2>
        <hr class="eser-heading-rule" />
        @if (filled($summary))
            <p class="eser-summary">{{ $summary }}</p>
        @endif

        @if ($items->isNotEmpty())
            <div class="eser-portrait-grid">
                @foreach ($items as $item)
                    @php
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    @endphp

                    <article>
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                width="104"
                                height="104"
                                loading="lazy"
                                decoding="async"
                                class="eser-portrait"
                            />
                        @endif

                        <h3 class="eser-portrait-name">
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p class="eser-portrait-bio">
                            {{ data_get($item, 'summary', data_get($item, 'bio', '')) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
