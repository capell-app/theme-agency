@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section class="luxury-section">
    <div class="luxury-section-inner">
        <p class="luxury-kicker">
            {{ __('capell-theme-quiet-luxury-retail::sections.listing.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-quiet-luxury-retail::sections.listing.heading')) }}
        </h2>
        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="luxury-grid">
                @foreach ($items as $item)
                    <article class="luxury-card">
                        <p class="luxury-meta">
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
            <p class="luxury-lede">
                {{ __('capell-theme-quiet-luxury-retail::sections.listing.empty') }}
            </p>
        @endif
    </div>
</section>
