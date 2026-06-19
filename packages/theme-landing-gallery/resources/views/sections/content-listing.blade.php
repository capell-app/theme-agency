@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section class="editorial-section">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-landing-gallery::sections.listing.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-landing-gallery::sections.listing.heading')) }}
        </h2>
        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="editorial-grid">
                @foreach ($items as $item)
                    <article class="editorial-card">
                        <p class="editorial-meta">
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
            <p class="editorial-lede">
                {{ __('capell-theme-landing-gallery::sections.listing.empty') }}
            </p>
        @endif
    </div>
</section>
