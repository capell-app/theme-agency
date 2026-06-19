@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section class="outdoor-section">
    <div class="outdoor-section-inner">
        <p class="outdoor-kicker">
            {{ __('capell-theme-outdoor-mission::sections.listing.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-outdoor-mission::sections.listing.heading')) }}
        </h2>
        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="outdoor-grid">
                @foreach ($items as $item)
                    <article class="outdoor-card">
                        <p class="outdoor-meta">
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
            <p class="outdoor-lede">
                {{ __('capell-theme-outdoor-mission::sections.listing.empty') }}
            </p>
        @endif
    </div>
</section>
