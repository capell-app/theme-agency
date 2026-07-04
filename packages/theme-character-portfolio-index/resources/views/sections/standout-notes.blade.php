@php
    $heading = data_get($section, 'heading', __('capell-theme-character-portfolio-index::sections.updates.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-character-portfolio-index::sections.updates.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-character-portfolio-index::sections.updates.release_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.updates.release_summary')],
        ['title' => __('capell-theme-character-portfolio-index::sections.updates.beta_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.updates.beta_summary')],
    ]);
@endphp

<section
    id="standout-notes"
    class="cpi-section cpi-section-dark"
>
    <div class="cpi-section-inner">
        <p class="cpi-kicker">
            {{ __('capell-theme-character-portfolio-index::sections.updates.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="cpi-lede">{{ $summary }}</p>
        <div class="cpi-actions">
            <a
                class="cpi-button"
                href="{{ data_get($section, 'url', '#curated-grid') }}"
            >
                {{ data_get($section, 'label', __('capell-theme-character-portfolio-index::sections.updates.button')) }}
            </a>
        </div>

        <div class="cpi-index">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                @endphp

                <article class="cpi-index-row">
                    <span
                        class="cpi-index-numeral"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    @if (filled($itemImage))
                        <img
                            src="{{ $itemImage }}"
                            alt="{{ $itemAlt }}"
                            loading="lazy"
                            decoding="async"
                            class="cpi-index-thumb"
                        />
                    @endif

                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
