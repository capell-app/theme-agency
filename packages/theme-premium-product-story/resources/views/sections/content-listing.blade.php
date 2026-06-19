@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section class="product-section">
    <div class="product-section-inner">
        <p class="product-kicker">
            {{ __('capell-theme-premium-product-story::sections.listing.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-premium-product-story::sections.listing.heading')) }}
        </h2>
        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="product-grid">
                @foreach ($items as $item)
                    <article class="product-card">
                        <p class="product-meta">
                            {{ data_get($item, 'category', data_get($item, 'meta', '')) }}
                        </p>
                        <h3>
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @else
            <p class="product-lede">
                {{ __('capell-theme-premium-product-story::sections.listing.empty') }}
            </p>
        @endif
    </div>
</section>
