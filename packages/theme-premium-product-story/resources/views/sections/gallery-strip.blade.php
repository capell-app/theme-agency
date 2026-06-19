@php
    $stories = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-premium-product-story::sections.gallery.angle_title'), 'summary' => __('capell-theme-premium-product-story::sections.gallery.angle_summary')],
        ['title' => __('capell-theme-premium-product-story::sections.gallery.detail_title'), 'summary' => __('capell-theme-premium-product-story::sections.gallery.detail_summary')],
        ['title' => __('capell-theme-premium-product-story::sections.gallery.use_title'), 'summary' => __('capell-theme-premium-product-story::sections.gallery.use_summary')],
    ]));
@endphp

<section class="product-section">
    <div class="product-section-inner">
        <p class="product-kicker">
            {{ __('capell-theme-premium-product-story::sections.gallery.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-premium-product-story::sections.gallery.heading')) }}
        </h2>
        <div class="product-grid">
            @foreach ($stories as $story)
                <article class="product-card">
                    <p class="product-meta">
                        {{ data_get($story, 'meta', data_get($story, 'category', '')) }}
                    </p>
                    <h3>
                        {{ data_get($story, 'title', data_get($story, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($story, 'summary', data_get($story, 'description', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
