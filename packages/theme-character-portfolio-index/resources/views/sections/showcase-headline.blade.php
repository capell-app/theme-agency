@php
    $heading = data_get($section, 'heading', __('capell-theme-character-portfolio-index::sections.featured.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-character-portfolio-index::sections.featured.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-character-portfolio-index::sections.featured.launch_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.featured.launch_summary')],
        ['title' => __('capell-theme-character-portfolio-index::sections.featured.essay_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.featured.essay_summary')],
        ['title' => __('capell-theme-character-portfolio-index::sections.featured.template_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.featured.template_summary')],
    ]);
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', $heading);
@endphp

<section
    id="showcase-headline"
    class="cpi-section"
>
    <div class="cpi-section-inner cpi-split">
        <div>
            <p class="cpi-kicker">
                {{ __('capell-theme-character-portfolio-index::sections.featured.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="cpi-lede">{{ $summary }}</p>

            <div class="cpi-grid">
                @foreach ($items as $item)
                    <article class="cpi-card">
                        <h3>
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p>{{ data_get($item, 'summary', '') }}</p>
                    </article>
                @endforeach
            </div>
        </div>

        @if (filled($mediaUrl))
            <figure class="cpi-plate">
                <div class="cpi-plate-frame">
                    <img
                        src="{{ $mediaUrl }}"
                        alt="{{ $mediaAlt }}"
                        loading="lazy"
                        decoding="async"
                        class="cpi-plate-media cpi-plate-media-wide"
                    />
                </div>
            </figure>
        @endif
    </div>
</section>
