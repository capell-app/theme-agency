@php
    $stories = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-minimal-fashion::sections.materials.linen_title'), 'summary' => __('capell-theme-minimal-fashion::sections.materials.linen_summary'), 'meta' => __('capell-theme-minimal-fashion::sections.materials.linen_meta')],
        ['title' => __('capell-theme-minimal-fashion::sections.materials.wool_title'), 'summary' => __('capell-theme-minimal-fashion::sections.materials.wool_summary'), 'meta' => __('capell-theme-minimal-fashion::sections.materials.wool_meta')],
        ['title' => __('capell-theme-minimal-fashion::sections.materials.scent_title'), 'summary' => __('capell-theme-minimal-fashion::sections.materials.scent_summary'), 'meta' => __('capell-theme-minimal-fashion::sections.materials.scent_meta')],
    ]));
@endphp

<section class="fashion-section">
    <div class="fashion-section-inner">
        <p class="fashion-kicker">
            {{ __('capell-theme-minimal-fashion::sections.materials.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-minimal-fashion::sections.materials.heading')) }}
        </h2>
        <div class="fashion-grid">
            @foreach ($stories as $story)
                <article class="fashion-card">
                    <p class="fashion-meta">
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
