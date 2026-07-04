@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-dense-news-analysis::sections.proof.care_value'), 'label' => __('capell-theme-dense-news-analysis::sections.proof.care_label')],
        ['value' => __('capell-theme-dense-news-analysis::sections.proof.materials_value'), 'label' => __('capell-theme-dense-news-analysis::sections.proof.materials_label')],
        ['value' => __('capell-theme-dense-news-analysis::sections.proof.stores_value'), 'label' => __('capell-theme-dense-news-analysis::sections.proof.stores_label')],
    ]);
@endphp

<section class="dnews-section">
    <div class="dnews-section-inner">
        <div class="dnews-section-head">
            <p class="dnews-kicker">
                {{ __('capell-theme-dense-news-analysis::sections.proof.kicker') }}
            </p>
            @if (data_get($section, 'heading') !== null)
                <h2>{{ data_get($section, 'heading') }}</h2>
            @endif

            @if (data_get($section, 'summary') !== null)
                <p class="dnews-lede">
                    {{ data_get($section, 'summary') }}
                </p>
            @endif
        </div>

        <div class="dnews-desk">
            @foreach ($items as $item)
                <article>
                    <p class="dnews-meta">
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </p>
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
