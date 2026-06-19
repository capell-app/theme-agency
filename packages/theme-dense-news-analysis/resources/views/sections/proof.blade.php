@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-dense-news-analysis::sections.proof.care_value'), 'label' => __('capell-theme-dense-news-analysis::sections.proof.care_label')],
        ['value' => __('capell-theme-dense-news-analysis::sections.proof.materials_value'), 'label' => __('capell-theme-dense-news-analysis::sections.proof.materials_label')],
        ['value' => __('capell-theme-dense-news-analysis::sections.proof.stores_value'), 'label' => __('capell-theme-dense-news-analysis::sections.proof.stores_label')],
    ]);
@endphp

<section class="editorial-section">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-dense-news-analysis::sections.proof.kicker') }}
        </p>
        <div class="editorial-grid">
            @foreach ($items as $item)
                <article class="editorial-card">
                    <h3>
                        {{ data_get($item, 'value', data_get($item, 'title', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'label', data_get($item, 'summary', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
