@php
    $stories = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-quiet-luxury-retail::sections.materials.linen_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.materials.linen_summary'), 'meta' => __('capell-theme-quiet-luxury-retail::sections.materials.linen_meta')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.materials.wool_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.materials.wool_summary'), 'meta' => __('capell-theme-quiet-luxury-retail::sections.materials.wool_meta')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.materials.scent_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.materials.scent_summary'), 'meta' => __('capell-theme-quiet-luxury-retail::sections.materials.scent_meta')],
    ]));
@endphp

<section class="luxury-section">
    <div class="luxury-section-inner">
        <p class="luxury-kicker">
            {{ __('capell-theme-quiet-luxury-retail::sections.materials.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-quiet-luxury-retail::sections.materials.heading')) }}
        </h2>
        <div class="luxury-grid">
            @foreach ($stories as $story)
                <article class="luxury-card">
                    <p class="luxury-meta">
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
