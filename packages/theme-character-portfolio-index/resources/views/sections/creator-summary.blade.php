@php
    $heading = data_get($section, 'heading', __('capell-theme-character-portfolio-index::sections.authors.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-character-portfolio-index::sections.authors.summary'));
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-character-portfolio-index::sections.authors.design_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.authors.design_summary')],
        ['title' => __('capell-theme-character-portfolio-index::sections.authors.advice_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.authors.advice_summary')],
        ['title' => __('capell-theme-character-portfolio-index::sections.authors.company_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.authors.company_summary')],
    ]))->values();
    $spotlight = $items->first();
    $supporting = $items->slice(1);
    $spotlightImage = data_get($spotlight, 'image', data_get($spotlight, 'imageUrl'));
    $spotlightAlt = data_get($spotlight, 'imageAlt', data_get($spotlight, 'title', ''));
@endphp

<section
    id="creator-summary"
    class="cpi-section cpi-section-field"
>
    <div class="cpi-section-inner cpi-split">
        <figure class="cpi-plate">
            <div class="cpi-plate-frame">
                @if (filled($spotlightImage))
                    <img
                        src="{{ $spotlightImage }}"
                        alt="{{ $spotlightAlt }}"
                        loading="lazy"
                        decoding="async"
                        class="cpi-plate-media"
                    />
                @else
                    <div
                        class="cpi-plate-media cpi-plate-media-empty"
                        aria-hidden="true"
                    ></div>
                @endif
            </div>
        </figure>

        <div>
            <p class="cpi-kicker">
                {{ __('capell-theme-character-portfolio-index::sections.authors.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="cpi-lede">{{ $summary }}</p>

            @if ($spotlight)
                <article
                    class="cpi-card"
                    style="margin-top: 1.5rem"
                >
                    <h3>
                        {{ data_get($spotlight, 'title', data_get($spotlight, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($spotlight, 'summary', '') }}</p>
                </article>
            @endif

            @if ($supporting->isNotEmpty())
                <div class="cpi-grid">
                    @foreach ($supporting as $item)
                        <article class="cpi-card">
                            <h3>
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            </h3>
                            <p>{{ data_get($item, 'summary', '') }}</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
