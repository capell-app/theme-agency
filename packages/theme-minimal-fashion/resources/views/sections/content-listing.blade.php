@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section class="fashion-section">
    <div class="fashion-section-inner">
        <p class="fashion-kicker">
            {{ __('capell-theme-minimal-fashion::sections.listing.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-minimal-fashion::sections.listing.heading')) }}
        </h2>
        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="fashion-grid">
                @foreach ($items as $item)
                    <article class="fashion-card">
                        <p class="fashion-meta">
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
            <p class="fashion-lede">
                {{ __('capell-theme-minimal-fashion::sections.listing.empty') }}
            </p>
        @endif
    </div>
</section>
