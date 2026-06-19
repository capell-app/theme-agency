@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section class="sport-section">
    <div class="sport-section-inner">
        <p class="sport-kicker">
            {{ __('capell-theme-bold-sport-commerce::sections.listing.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-bold-sport-commerce::sections.listing.heading')) }}
        </h2>
        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="sport-grid">
                @foreach ($items as $item)
                    <article class="sport-card">
                        <p class="sport-meta">
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
            <p class="sport-lede">
                {{ __('capell-theme-bold-sport-commerce::sections.listing.empty') }}
            </p>
        @endif
    </div>
</section>
